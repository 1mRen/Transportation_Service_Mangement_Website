<?php
// Import the layout class
require_once '../../views/layout/layout.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: /src/views/auth/signin.php");
    exit();
}

// Check if reservation ID is provided
if (!isset($_GET['id'])) {
    header("Location: /src/views/reservations/index.php");
    exit();
}

// Create a new Layout instance
$layout = new Layout('Decline Reservation', 'reservations');

// Database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Get reservation details
$reservationId = $_GET['id'];
$query = "SELECT r.*, a.user_id, a.full_name, a.contact_no, a.email,
          v.plate_no, v.type_of_vehicle, v.capacity,
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

// Get approval documents
$docsQuery = "SELECT * FROM approval_document WHERE reservation_id = ?";
$docsStmt = mysqli_prepare($conn, $docsQuery);
mysqli_stmt_bind_param($docsStmt, "i", $reservationId);
mysqli_stmt_execute($docsStmt);
$docsResult = mysqli_stmt_get_result($docsStmt);
$approvalDocs = mysqli_fetch_all($docsResult, MYSQLI_ASSOC);

// Get assigned vehicles
$vehiclesQuery = "SELECT v.* FROM vehicle v 
                 JOIN reservation_vehicles rv ON v.vehicle_id = rv.vehicle_id 
                 WHERE rv.reservation_id = ?";
$vehiclesStmt = mysqli_prepare($conn, $vehiclesQuery);
mysqli_stmt_bind_param($vehiclesStmt, "i", $reservationId);
mysqli_stmt_execute($vehiclesStmt);
$vehiclesResult = mysqli_stmt_get_result($vehiclesStmt);
$assignedVehicles = mysqli_fetch_all($vehiclesResult, MYSQLI_ASSOC);

// Check if reservation is pending
if (strtolower($reservation['status_name']) !== 'pending') {
    header("Location: /src/views/reservations/view.php?id=" . $reservationId);
    exit();
}

// Handle form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remarks = $_POST['remarks'] ?? '';

    if (empty($remarks)) {
        $error = "Please provide a reason for declining the reservation.";
    } else {
        // Get declined status ID
        $statusQuery = "SELECT status_id FROM reservation_status WHERE status_name = 'Rejected'";
        $statusResult = mysqli_query($conn, $statusQuery);
        $status = mysqli_fetch_assoc($statusResult);

        if ($status) {
            // Update reservation status
            $updateQuery = "UPDATE reservation SET status_id = ?, remarks = ? WHERE reservation_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "isi", $status['status_id'], $remarks, $reservationId);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $success = true;
                // Redirect to view page after 2 seconds
                header("refresh:2;url=/src/views/reservations/view.php?id=" . $reservationId);

                // Notify user of rejection
                require_once __DIR__ . '/../../controllers/NotificationController.php';
                $notificationController = new \Controllers\NotificationController();
                $title = 'Reservation Rejected';
                $message = 'Your reservation (ID: ' . $reservationId . ') for vehicle ' . $reservation['type_of_vehicle'] . ' (' . $reservation['plate_no'] . ') has been rejected.';
                $notificationController->create($reservation['user_id'], $title, $message, 'danger');
            } else {
                $error = "Failed to reject reservation.";
            }
        } else {
            $error = "Status not found.";
        }
    }
}

// Render the header
$layout->renderHeader();
?>

<!-- Page Header -->
<div class="page-header">
    <h1>Reject Reservation</h1>
    <div class="header-actions">
        <a href="/src/views/reservations/index.php" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Reservation
        </a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Reservation rejected successfully! Redirecting...
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
                                                <a href="<?php echo $doc['file_path']; ?>" target="_blank" download>
                                                    <i class="fa fa-file"></i>
                                                </a>
                                            <?php endif; ?>
                                            <div style="font-size:0.85em;margin-top:4px;word-break:break-all;">
                                                <?php echo htmlspecialchars($doc['original_name'] ?? basename($doc['file_path'])); ?>
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

        <!-- Reject Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Reject Reservation</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Reason for Rejection</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3" required
                                  placeholder="Please provide a reason for rejecting this reservation..."></textarea>
                        <div class="invalid-feedback">Please provide a reason for rejecting the reservation.</div>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fa fa-times"></i> Reject Reservation
                    </button>
                </form>
            </div>
        </div>
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