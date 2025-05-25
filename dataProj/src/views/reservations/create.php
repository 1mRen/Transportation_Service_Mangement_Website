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
$layout = new Layout('Create Reservation', 'reservations');

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

// Vehicle types
$vehicleTypes = ['Bus', 'Coaster', 'Canter', 'Private Vehicle'];
$vehicleType = $_POST['vehicle_type'] ?? '';

// Get available vehicles for the selected date
$dateOfUse = $_POST['date_of_use'] ?? '';
$departureTime = $_POST['departure_time'] ?? '';
$returnTime = $_POST['return_time'] ?? '';
$departureArea = $_POST['departure_area'] ?? '';
$destination = $_POST['destination'] ?? '';
$purpose = $_POST['purpose'] ?? '';
$numberOfPeople = $_POST['number_of_people'] ?? '';
$numberOfVehicles = $_POST['number_of_vehicles'] ?? '';
$numberOfDays = $_POST['number_of_days'] ?? '';

$availableVehicles = [];
if ($dateOfUse && $departureTime && $returnTime) {
    $query = "SELECT v.* 
        FROM vehicle v 
        WHERE v.vehicle_id NOT IN (
            SELECT r.vehicle_id 
            FROM reservation r 
            WHERE r.date_of_use = ? 
            AND r.status_id IN (1, 2) 
            AND (
                (r.departure_time <= ? AND r.return_time >= ?) OR
                (r.departure_time <= ? AND r.return_time >= ?) OR
                (r.departure_time >= ? AND r.return_time <= ?)
            )
        )";
    if ($vehicleType) {
        $query .= " AND v.type_of_vehicle = '" . mysqli_real_escape_string($conn, $vehicleType) . "'";
    }
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssss", 
        $dateOfUse, 
        $departureTime, $departureTime,
        $returnTime, $returnTime,
        $departureTime, $returnTime
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $availableVehicles[] = $row;
    }
}

// Handle form submission
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

// Render the header
$layout->renderHeader();
?>

<!-- Page Header -->
<div class="page-header">
    <h1>Create New Reservation</h1>
    <div class="header-actions">
        <a href="/src/views/reservations/my-reservations.php" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Reservations
        </a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Reservation created successfully! Redirecting to your reservations...
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Reservation Form -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Create New Reservation</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
            <div class="row g-3">
                <!-- Vehicle Type -->
                <div class="col-md-6">
                    <label for="vehicle_type" class="form-label">Vehicle Type</label>
                    <select name="vehicle_type" id="vehicle_type" class="form-select" required>
                        <option value="">Select vehicle type...</option>
                        <?php foreach ($vehicleTypes as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" 
                                    <?php echo $vehicleType === $type ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a vehicle type.</div>
                </div>
                <!-- Number of People -->
                <div class="col-md-6">
                    <label for="number_of_people" class="form-label">Number of People</label>
                    <input type="number" class="form-control" id="number_of_people" name="number_of_people" min="1" value="<?php echo htmlspecialchars($numberOfPeople); ?>" required>
                    <div class="invalid-feedback">Please enter the number of people.</div>
                </div>

                <!-- Number of Vehicles -->
                <div class="col-md-6">
                    <label for="number_of_vehicles" class="form-label">Number of Vehicles Needed</label>
                    <select name="number_of_vehicles" id="number_of_vehicles" class="form-select" required>
                        <option value="">Select number...</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $numberOfVehicles == $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?> vehicle<?php echo $i > 1 ? 's' : ''; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <div class="invalid-feedback">Please select the number of vehicles needed (maximum 5).</div>
                </div>

                <!-- Number of Days -->
                <div class="col-md-6">
                    <label for="number_of_days" class="form-label">Number of Days</label>
                    <input type="number" class="form-control" id="number_of_days" name="number_of_days" min="1" value="<?php echo htmlspecialchars($numberOfDays); ?>" required>
                    <div class="invalid-feedback">Please enter the number of days.</div>
                </div>

                <!-- Date of Use -->
                <div class="col-md-6">
                    <label for="date_of_use" class="form-label">Date of Use</label>
                    <input type="date" class="form-control" id="date_of_use" name="date_of_use" value="<?php echo $dateOfUse; ?>" required min="<?php echo date('Y-m-d'); ?>">
                    <div class="invalid-feedback">Please select a date.</div>
                </div>

                <!-- Time -->
                <div class="col-md-6">
                    <label for="departure_time" class="form-label">Departure Time</label>
                    <input type="time" class="form-control" id="departure_time" name="departure_time" value="<?php echo $departureTime; ?>" required>
                    <div class="invalid-feedback">Please select departure time.</div>
                </div>

                <div class="col-md-6">
                    <label for="return_time" class="form-label">Return Time</label>
                    <input type="time" class="form-control" id="return_time" name="return_time" value="<?php echo $returnTime; ?>" required>
                    <div class="invalid-feedback">Please select return time.</div>
                </div>

                <!-- Other Fields -->
                <div class="col-md-6">
                    <label for="departure_area" class="form-label">Departure Area</label>
                    <input type="text" class="form-control" id="departure_area" name="departure_area" 
                           value="<?php echo $departureArea; ?>" required>
                    <div class="invalid-feedback">Please enter departure area.</div>
                </div>

                <div class="col-md-6">
                    <label for="destination" class="form-label">Destination</label>
                    <input type="text" class="form-control" id="destination" name="destination" 
                           value="<?php echo $destination; ?>" required>
                    <div class="invalid-feedback">Please enter destination.</div>
                </div>

                <div class="col-12">
                    <label for="purpose" class="form-label">Purpose</label>
                    <textarea class="form-control" id="purpose" name="purpose" rows="3" required><?php echo $purpose; ?></textarea>
                    <div class="invalid-feedback">Please enter purpose.</div>
                </div>

                <!-- Approval Document Upload -->
                <div class="col-12">
                    <label for="approval_document" class="form-label">Approval Documents (PDF or Images)</label>
                    <input type="file" class="form-control" id="approval_document" name="approval_document[]" accept=".pdf,image/*" multiple required onchange="previewFiles()">
                    <div class="invalid-feedback">Please upload at least one approval document (PDF or image).</div>
                    <div id="file-preview" class="mt-3"></div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Create Reservation</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.form-label {
    margin-bottom: 0.25rem;
}

.form-check {
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    margin-bottom: 0.5rem;
}

.form-check:hover {
    background-color: #f8f9fa;
}
</style>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
    })
})()

// Time validation
document.getElementById('return_time').addEventListener('change', function() {
    const departureTime = document.getElementById('departure_time').value;
    const returnTime = this.value;
    
    if (departureTime && returnTime && departureTime >= returnTime) {
        alert('Return time must be after departure time.');
        this.value = '';
    }
});

// Preview uploaded files
function previewFiles() {
    const preview = document.getElementById('file-preview');
    const input = document.getElementById('approval_document');
    preview.innerHTML = '';
    if (!input.files.length) return;
    Array.from(input.files).forEach(file => {
        const fileDiv = document.createElement('div');
        fileDiv.style.display = 'inline-block';
        fileDiv.style.marginRight = '10px';
        fileDiv.style.marginBottom = '10px';
        fileDiv.style.textAlign = 'center';
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.style.width = '80px';
            img.style.height = '80px';
            img.style.objectFit = 'cover';
            img.style.border = '1px solid #ccc';
            img.style.borderRadius = '6px';
            img.title = file.name;
            fileDiv.appendChild(img);
            const reader = new FileReader();
            reader.onload = e => img.src = e.target.result;
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            const icon = document.createElement('div');
            icon.innerHTML = '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d32f2f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" fill="#fff"/><path d="M7 7h10M7 11h10M7 15h6"/><rect x="7" y="15" width="6" height="2" fill="#d32f2f"/></svg>';
            fileDiv.appendChild(icon);
        } else {
            const span = document.createElement('span');
            span.textContent = file.name;
            fileDiv.appendChild(span);
        }
        const label = document.createElement('div');
        label.style.fontSize = '0.85em';
        label.style.marginTop = '4px';
        label.textContent = file.name;
        fileDiv.appendChild(label);
        preview.appendChild(fileDiv);
    });
}
</script>

<?php
// Render the footer
$layout->renderFooter();
?> 