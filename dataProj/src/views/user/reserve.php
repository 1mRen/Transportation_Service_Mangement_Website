<?php
// Import the layout class
require_once '../../views/layout/layout.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: /src/views/auth/signin.php");
    exit();
}

// Create a new Layout instance
$layout = new Layout('Reserve Vehicle', 'user');

// Database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Get user's applicant ID
$userId = $_SESSION['id'];
$applicantQuery = "SELECT applicant_id FROM applicant WHERE user_id = ?";
$applicantStmt = mysqli_prepare($conn, $applicantQuery);
mysqli_stmt_bind_param($applicantStmt, "i", $userId);
mysqli_stmt_execute($applicantStmt);
$applicantResult = mysqli_stmt_get_result($applicantStmt);
$applicant = mysqli_fetch_assoc($applicantResult);

// If no applicant profile exists, redirect to create profile
if (!$applicant) {
    $_SESSION['error'] = "Please complete your applicant profile before making a reservation.";
    header("Location: /src/views/applicant/create.php");
    exit();
}

$applicantId = $applicant['applicant_id'];

$vehicleTypes = ['Bus', 'Coaster', 'Canter', 'Private Vehicle'];
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateOfUse = $_POST['date_of_use'] ?? '';
    $departureTime = $_POST['departure_time'] ?? '';
    $returnTime = $_POST['return_time'] ?? '';
    $departureArea = $_POST['departure_area'] ?? '';
    $destination = $_POST['destination'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    $vehicleType = $_POST['vehicle_type'] ?? '';
    $numberOfPeople = $_POST['number_of_people'] ?? '';
    $numberOfVehicles = $_POST['number_of_vehicles'] ?? '';
    $numberOfDays = $_POST['number_of_days'] ?? '';

    // Validate required fields
    if (empty($dateOfUse) || empty($departureTime) || empty($returnTime) || 
        empty($departureArea) || empty($destination) || empty($purpose) || empty($vehicleType) || 
        empty($numberOfPeople) || empty($numberOfVehicles) || empty($numberOfDays)) {
        $error = "All fields are required.";
    } elseif ($numberOfVehicles > 5) {
        $error = "Maximum number of vehicles allowed is 5.";
    } elseif (!isset($_FILES['approval_document']) || $_FILES['approval_document']['error'][0] == UPLOAD_ERR_NO_FILE) {
        $error = "Please upload at least one approval document (PDF or image).";
    } elseif (!in_array($vehicleType, $vehicleTypes)) {
        $error = "Invalid vehicle type selected.";
    } else {
        // Validate file types
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $allFilesValid = true;
        foreach ($_FILES['approval_document']['type'] as $type) {
            if (!in_array($type, $allowedTypes)) {
                $allFilesValid = false;
                break;
            }
        }
        if (!$allFilesValid) {
            $error = "Invalid file type detected. Only PDF and image files are allowed.";
        } else {
            // Start transaction
            mysqli_begin_transaction($conn);
            try {
                // Insert reservation with vehicle_id as NULL
                $insertQuery = "INSERT INTO reservation (applicant_id, vehicle_id, date_of_use, departure_time, 
                              return_time, departure_area, destination, purpose, number_of_people, number_of_vehicles, 
                              number_of_days, type_of_vehicle, status_id) 
                              VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt = mysqli_prepare($conn, $insertQuery);
                mysqli_stmt_bind_param($stmt, "issssssiiis", 
                    $applicantId,
                    $dateOfUse,
                    $departureTime,
                    $returnTime,
                    $departureArea,
                    $destination,
                    $purpose,
                    $numberOfPeople,
                    $numberOfVehicles,
                    $numberOfDays,
                    $vehicleType
                );
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to create reservation.");
                }
                $reservationId = mysqli_insert_id($conn);

                // Add a note in remarks about temporary vehicle assignment
                $remarksQuery = "UPDATE reservation SET remarks = 'Vehicle assignment pending admin approval' WHERE reservation_id = ?";
                $remarksStmt = mysqli_prepare($conn, $remarksQuery);
                mysqli_stmt_bind_param($remarksStmt, "i", $reservationId);
                mysqli_stmt_execute($remarksStmt);

                // Handle multiple file uploads
                $uploadDir = __DIR__ . '/../../../uploads/approval_documents/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                foreach ($_FILES['approval_document']['tmp_name'] as $idx => $fileTmp) {
                    $fileName = basename($_FILES['approval_document']['name'][$idx]);
                    $fileType = $_FILES['approval_document']['type'][$idx];
                    $filePath = $uploadDir . uniqid() . '_' . $fileName;
                    if (!move_uploaded_file($fileTmp, $filePath)) {
                        throw new Exception("Failed to upload approval document.");
                    }
                    // Save document info
                    $relativePath = '/uploads/approval_documents/' . basename($filePath);
                    $docQuery = "INSERT INTO approval_document (reservation_id, file_path, original_name, file_type) VALUES (?, ?, ?, ?)";
                    $docStmt = mysqli_prepare($conn, $docQuery);
                    mysqli_stmt_bind_param($docStmt, "isss", $reservationId, $relativePath, $fileName, $fileType);
                    if (!mysqli_stmt_execute($docStmt)) {
                        throw new Exception("Failed to save approval document info.");
                    }
                }
                mysqli_commit($conn);
                $success = true;
                // Notify all admins about the new reservation
                require_once __DIR__ . '/../../controllers/NotificationController.php';
                $notificationController = new \Controllers\NotificationController();
                $title = 'New Reservation Request';
                $message = 'A new reservation has been submitted and is pending approval.';
                $notificationController->notifyAdmins($title, $message, 'info');
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = $e->getMessage();
            }
        }
    }
}

$layout->renderHeader();
?>
<div class="container mt-4">
    <h2>Reserve a Vehicle</h2>
    <?php if ($success): ?>
        <div class="alert alert-success">Reservation submitted successfully! <a href="/src/views/reservations/my-reservations.php">View My Reservations</a></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-4">
            <label for="vehicle_type" class="form-label">Vehicle Type</label>
            <select name="vehicle_type" id="vehicle_type" class="form-select" required>
                <option value="">Select vehicle type</option>
                <?php foreach ($vehicleTypes as $type): ?>
                    <option value="<?php echo $type; ?>" <?php if (($vehicleType ?? '') === $type) echo 'selected'; ?>><?php echo $type; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="date_of_use" class="form-label">Date of Use</label>
            <input type="date" name="date_of_use" id="date_of_use" class="form-control" value="<?php echo htmlspecialchars($dateOfUse ?? ''); ?>" required>
        </div>
        <div class="col-md-4">
            <label for="number_of_days" class="form-label">Number of Days</label>
            <input type="number" name="number_of_days" id="number_of_days" class="form-control" min="1" max="30" value="<?php echo htmlspecialchars($numberOfDays ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="departure_time" class="form-label">Departure Time</label>
            <input type="time" name="departure_time" id="departure_time" class="form-control" value="<?php echo htmlspecialchars($departureTime ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="return_time" class="form-label">Return Time</label>
            <input type="time" name="return_time" id="return_time" class="form-control" value="<?php echo htmlspecialchars($returnTime ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="departure_area" class="form-label">Departure Area</label>
            <input type="text" name="departure_area" id="departure_area" class="form-control" value="<?php echo htmlspecialchars($departureArea ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label for="destination" class="form-label">Destination</label>
            <input type="text" name="destination" id="destination" class="form-control" value="<?php echo htmlspecialchars($destination ?? ''); ?>" required>
        </div>
        <div class="col-md-4">
            <label for="number_of_people" class="form-label">Number of People</label>
            <input type="number" name="number_of_people" id="number_of_people" class="form-control" min="1" max="100" value="<?php echo htmlspecialchars($numberOfPeople ?? ''); ?>" required>
        </div>
        <div class="col-md-4">
            <label for="number_of_vehicles" class="form-label">Number of Vehicles</label>
            <input type="number" name="number_of_vehicles" id="number_of_vehicles" class="form-control" min="1" max="5" value="<?php echo htmlspecialchars($numberOfVehicles ?? ''); ?>" required>
        </div>
        <div class="col-md-12">
            <label for="purpose" class="form-label">Purpose</label>
            <textarea name="purpose" id="purpose" class="form-control" rows="2" required><?php echo htmlspecialchars($purpose ?? ''); ?></textarea>
        </div>
        <div class="col-md-12">
            <label for="approval_document" class="form-label">Approval Document(s) (PDF or Image)</label>
            <input type="file" name="approval_document[]" id="approval_document" class="form-control" accept="application/pdf,image/*" multiple required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Submit Reservation</button>
        </div>
    </form>
</div>
<?php $layout->renderFooter(); ?> 