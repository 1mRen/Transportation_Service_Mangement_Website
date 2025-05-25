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

// Check if reservation ID is provided
if (!isset($_GET['id'])) {
    header("Location: /src/views/reservations/my-reservations.php");
    exit();
}

// Create a new Layout instance
$layout = new Layout('View Reservation', 'reservations');

// Database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Get reservation details with vehicle information
$reservationId = $_GET['id'];
$query = "SELECT r.*, a.full_name, a.contact_no, a.email, a.organization_department, a.position, a.user_id,
          v.plate_no, v.type_of_vehicle AS vehicle_type_actual, v.capacity,
          rs.status_name
          FROM reservation r
          JOIN applicant a ON r.applicant_id = a.applicant_id
          LEFT JOIN vehicle v ON r.vehicle_id = v.vehicle_id
          JOIN reservation_status rs ON r.status_id = rs.status_id
          WHERE r.reservation_id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $reservationId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$reservation = mysqli_fetch_assoc($result);

if (!$reservation) {
    die("Reservation not found.");
}

// Check if user has permission to view this reservation
$isAdmin = strtolower($_SESSION['role']) === 'admin';
$isOwner = $reservation['user_id'] == $_SESSION['id'];

if (!$isAdmin && !$isOwner) {
    header("Location: /src/views/reservations/my-reservations.php");
    exit();
}

// Handle status update (admin only)
$success = false;
$error = '';

// Get available vehicles with 'In Use' status and matching type
$availableVehiclesQuery = "SELECT v.*, vs.status_name 
                          FROM vehicle v 
                          JOIN vehicle_status vs ON v.status_id = vs.status_id 
                          WHERE vs.status_name = 'In Use' 
                          AND v.type_of_vehicle = ?
                          AND v.vehicle_id NOT IN (
                              SELECT r.vehicle_id 
                              FROM reservation r 
                              WHERE r.date_of_use = ? 
                              AND r.status_id IN (1, 2) 
                              AND r.reservation_id != ?
                              AND (
                                  (r.departure_time <= ? AND r.return_time >= ?) OR
                                  (r.departure_time <= ? AND r.return_time >= ?) OR
                                  (r.departure_time >= ? AND r.return_time <= ?)
                              )
                          )";
$stmt = mysqli_prepare($conn, $availableVehiclesQuery);
mysqli_stmt_bind_param($stmt, "sisssssss", 
    $reservation['type_of_vehicle'],
    $reservation['date_of_use'],
    $reservationId,
    $reservation['departure_time'], $reservation['departure_time'],
    $reservation['return_time'], $reservation['return_time'],
    $reservation['departure_time'], $reservation['return_time']
);
mysqli_stmt_execute($stmt);
$availableVehicles = mysqli_stmt_get_result($stmt);

// Fetch approval documents for this reservation
$docQuery = "SELECT * FROM approval_document WHERE reservation_id = ?";
$docStmt = mysqli_prepare($conn, $docQuery);
mysqli_stmt_bind_param($docStmt, "i", $reservationId);
mysqli_stmt_execute($docStmt);
$docsResult = mysqli_stmt_get_result($docStmt);
$approvalDocs = [];
while ($doc = mysqli_fetch_assoc($docsResult)) {
    $approvalDocs[] = $doc;
}

// After fetching $reservation, add this to fetch all assigned vehicles:
$vehiclesQuery = "SELECT v.plate_no, v.type_of_vehicle, v.capacity
                  FROM reservation_vehicles rv
                  JOIN vehicle v ON rv.vehicle_id = v.vehicle_id
                  WHERE rv.reservation_id = ?";
$vehiclesStmt = mysqli_prepare($conn, $vehiclesQuery);
mysqli_stmt_bind_param($vehiclesStmt, "i", $reservationId);
mysqli_stmt_execute($vehiclesStmt);
$vehiclesResult = mysqli_stmt_get_result($vehiclesStmt);
$assignedVehicles = [];
while ($vehicle = mysqli_fetch_assoc($vehiclesResult)) {
    $assignedVehicles[] = $vehicle;
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $newVehicleId = $_POST['vehicle_id'] ?? $reservation['vehicle_id'];

    if (empty($newStatus)) {
        $error = "Please select a status.";
    } else {
        // Get status ID
        $statusQuery = "SELECT status_id FROM reservation_status WHERE status_name = ?";
        $statusStmt = mysqli_prepare($conn, $statusQuery);
        mysqli_stmt_bind_param($statusStmt, "s", $newStatus);
        mysqli_stmt_execute($statusStmt);
        $statusResult = mysqli_stmt_get_result($statusStmt);
        $status = mysqli_fetch_assoc($statusResult);

        if ($status) {
            // Update reservation status, remarks, and vehicle
            $updateQuery = "UPDATE reservation SET status_id = ?, remarks = ?, vehicle_id = ? WHERE reservation_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "isii", $status['status_id'], $remarks, $newVehicleId, $reservationId);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $success = true;
                // Notify user of status change
                require_once __DIR__ . '/../../controllers/NotificationController.php';
                $notificationController = new \Controllers\NotificationController();
                $title = 'Reservation Status Updated';
                $message = 'Your reservation (ID: ' . $reservationId . ') for vehicle ' . $reservation['type_of_vehicle'] . ' (' . $reservation['plate_no'] . ') has been updated to ' . $newStatus . '.';
                $type = strtolower($newStatus) === 'approved' ? 'success' : (strtolower($newStatus) === 'rejected' ? 'danger' : 'info');
                $notificationController->create($reservation['user_id'], $title, $message, $type);
                // Refresh the page to show updated status
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                $error = "Failed to update status.";
            }
        } else {
            $error = "Invalid status selected.";
        }
    }
}

// Render the header
$layout->renderHeader();
?>

<!-- Page Header -->
<div class="page-header">
    <h1>Reservation Details</h1>
    <div class="header-actions">
        <?php if ($isAdmin): ?>
            <a href="/src/views/reservations/index.php" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to Reservations
            </a>
        <?php else: ?>
            <a href="/src/views/reservations/my-reservations.php" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to My Reservations
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Reservation status updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Reservation Details -->
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Reservation Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reservation ID</label>
                            <p><?php echo $reservation['reservation_id']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p>
                                <span class="badge bg-<?php 
                                    echo strtolower($reservation['status_name']) === 'pending' ? 'warning' : 
                                        (strtolower($reservation['status_name']) === 'approved' ? 'success' : 'danger'); 
                                ?>">
                                    <?php echo $reservation['status_name']; ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date of Use</label>
                            <p><?php echo date('F d, Y', strtotime($reservation['date_of_use'])); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Time</label>
                            <p>
                                <?php echo date('h:i A', strtotime($reservation['departure_time'])); ?> - 
                                <?php echo date('h:i A', strtotime($reservation['return_time'])); ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Departure Area</label>
                            <p><?php echo $reservation['departure_area']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Destination</label>
                            <p><?php echo $reservation['destination']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Number of People</label>
                            <p><?php echo htmlspecialchars($reservation['number_of_people'] ?? ''); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Vehicle Type</label>
                            <p><?php echo htmlspecialchars($reservation['type_of_vehicle']); ?></p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Purpose</label>
                            <p><?php echo nl2br($reservation['purpose']); ?></p>
                        </div>
                    </div>
                    <?php if (!empty($reservation['remarks'])): ?>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Remarks</label>
                                <p><?php echo nl2br($reservation['remarks']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- Approval Documents Section -->
                    <?php if (!empty($approvalDocs)): ?>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Approval Documents</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <?php foreach ($approvalDocs as $doc): ?>
                                        <div style="text-align:center;">
                                            <?php if (strpos($doc['file_type'], 'image/') === 0): ?>
                                                <a href="<?php echo $doc['file_path']; ?>" target="_blank" download>
                                                    <img src="<?php echo $doc['file_path']; ?>" alt="Document" style="width:80px;height:80px;object-fit:cover;border:1px solid #ccc;border-radius:6px;">
                                                </a>
                                            <?php elseif ($doc['file_type'] === 'application/pdf'): ?>
                                                <a href="<?php echo $doc['file_path']; ?>" target="_blank" download>
                                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d32f2f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" fill="#fff"/><path d="M7 7h10M7 11h10M7 15h6"/><rect x="7" y="15" width="6" height="2" fill="#d32f2f"/></svg>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo $doc['file_path']; ?>" target="_blank" download><?php echo htmlspecialchars($doc['original_name']); ?></a>
                                            <?php endif; ?>
                                            <div style="font-size:0.85em;margin-top:4px;word-break:break-all;">
                                                <?php echo htmlspecialchars($doc['original_name']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- Requested Vehicles and Days -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Requested Vehicles:</label>
                            <p class="mb-0">
                                <?php echo $reservation['number_of_vehicles']; ?> vehicle<?php echo $reservation['number_of_vehicles'] > 1 ? 's' : ''; ?> 
                                (<?php echo $reservation['type_of_vehicle']; ?>)
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Duration:</label>
                            <p class="mb-0">
                                <?php echo $reservation['number_of_days']; ?> day<?php echo $reservation['number_of_days'] > 1 ? 's' : ''; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Vehicle Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Vehicle Information</h5>
            </div>
            <div class="card-body">
                    <div class="mb-3">
                    <label class="form-label fw-bold">Assigned Vehicle(s)</label>
                    <?php if (!empty($assignedVehicles)): ?>
                        <ul>
                            <?php foreach ($assignedVehicles as $vehicle): ?>
                                <li>
                                    <i class="fa fa-car"></i>
                                    <?php echo htmlspecialchars($vehicle['type_of_vehicle']) . ' (' . htmlspecialchars($vehicle['plate_no']) . ')'; ?>
                                    <?php if (!empty($vehicle['capacity'])): ?>
                                        - Capacity: <?php echo htmlspecialchars($vehicle['capacity']); ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                <?php else: ?>
                        <span class="text-muted">Not assigned yet</span>
                <?php endif; ?>
                    </div>
            </div>
        </div>

        <!-- Applicant Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Applicant Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Name</label>
                    <p><?php echo $reservation['full_name']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Contact Number</label>
                    <p><?php echo $reservation['contact_no']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <p><?php echo $reservation['email']; ?></p>
                </div>
            </div>
        </div>

        <!-- Vehicle Assignment Form -->
        <?php if ($isAdmin && $reservation['status_name'] === 'Pending'): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Vehicle Assignment</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        Requested Vehicles: <?php echo $reservation['number_of_vehicles']; ?> vehicle<?php echo $reservation['number_of_vehicles'] > 1 ? 's' : ''; ?> 
                        (<?php echo htmlspecialchars($reservation['type_of_vehicle']); ?>)
                    </div>
                    <form method="POST" action="/src/views/reservations/assign-vehicle.php" class="needs-validation" novalidate>
                        <input type="hidden" name="reservation_id" value="<?php echo $reservationId; ?>">
                        <input type="hidden" name="requested_vehicles" value="<?php echo $reservation['number_of_vehicles']; ?>">
                        <input type="hidden" name="vehicle_type" value="<?php echo htmlspecialchars($reservation['type_of_vehicle']); ?>">
                        
                        <?php for ($i = 1; $i <= $reservation['number_of_vehicles']; $i++): ?>
                            <div class="mb-3">
                                <label for="vehicle_id_<?php echo $i; ?>" class="form-label">
                                    Vehicle <?php echo $i; ?> (<?php echo htmlspecialchars($reservation['type_of_vehicle']); ?>)
                                </label>
                                <select name="vehicle_ids[]" id="vehicle_id_<?php echo $i; ?>" class="form-select vehicle-select">
                                    <option value="">Select a <?php echo htmlspecialchars($reservation['type_of_vehicle']); ?>...</option>
                                    <?php 
                                    mysqli_data_seek($availableVehicles, 0);
                                    while ($vehicle = mysqli_fetch_assoc($availableVehicles)): 
                                    ?>
                                        <option value="<?php echo $vehicle['vehicle_id']; ?>" 
                                                data-type="<?php echo htmlspecialchars($vehicle['type_of_vehicle']); ?>"
                                                <?php 
                                                $assignedIds = explode(',', $reservation['assigned_vehicle_ids'] ?? '');
                                                echo in_array($vehicle['vehicle_id'], $assignedIds) ? 'selected' : ''; 
                                                ?>>
                                            <?php echo htmlspecialchars($vehicle['type_of_vehicle']) . ' - ' . htmlspecialchars($vehicle['plate_no']); ?>
                                            (<?php echo htmlspecialchars($vehicle['status_name']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        <?php endfor; ?>
                        
                        <div class="form-text mb-3">
                            Only <?php echo htmlspecialchars($reservation['type_of_vehicle']); ?> vehicles with 'In Use' status can be assigned. 
                            Make sure to select the correct number of vehicles as requested.
                        </div>
                        <button type="submit" class="btn btn-primary">Assign Vehicles</button>
                    </form>
                </div>
            </div>

            <style>
            .assigned-vehicles {
                background-color: #f8f9fa;
                padding: 1rem;
                border-radius: 0.25rem;
                border: 1px solid #dee2e6;
            }
            .vehicle-item {
                padding: 0.5rem;
                border-bottom: 1px solid #dee2e6;
            }
            .vehicle-item:last-child {
                border-bottom: none;
            }
            .vehicle-item i {
                margin-right: 0.5rem;
                color: #0d6efd;
            }
            </style>

            <script>
            // Prevent duplicate vehicle selection
            document.querySelectorAll('.vehicle-select').forEach(select => {
                select.addEventListener('change', function() {
                    const selectedValue = this.value;
                    const selectedType = this.options[this.selectedIndex].dataset.type;
                    const requestedType = '<?php echo htmlspecialchars($reservation['type_of_vehicle']); ?>';
                    
                    // Check other selects for the same value
                    document.querySelectorAll('.vehicle-select').forEach(otherSelect => {
                        if (otherSelect !== this && otherSelect.value === selectedValue) {
                            alert('This vehicle has already been selected. Please choose a different vehicle.');
                            this.value = '';
                            return;
                        }
                    });
                    
                    // Validate vehicle type matches the requested type
                    if (selectedValue && selectedType !== requestedType) {
                        alert('Please select a ' + requestedType + ' vehicle.');
                        this.value = '';
                        return;
                    }
                });
            });
            </script>
        <?php endif; ?>
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

.badge {
    padding: 0.5em 0.75em;
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
</script>

<?php
// Render the footer
$layout->renderFooter();
?> 