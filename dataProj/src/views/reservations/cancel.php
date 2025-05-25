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
$layout = new Layout('Cancel Reservation', 'reservations');

// Database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Get reservation details
$reservationId = $_GET['id'];
$query = "SELECT r.*, a.full_name as applicant_name, a.contact_no, a.email,
          v.plate_no, v.type_of_vehicle, v.capacity,
          rs.status_name as reservation_status
          FROM reservation r
          JOIN applicant a ON r.applicant_id = a.applicant_id
          JOIN vehicle v ON r.vehicle_id = v.vehicle_id
          JOIN reservation_status rs ON r.status_id = rs.status_id
          WHERE r.reservation_id = ? AND a.user_id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $reservationId, $_SESSION['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$reservation = mysqli_fetch_assoc($result);

if (!$reservation) {
    die("Reservation not found.");
}

// Check if reservation is pending
if ($reservation['reservation_status'] !== 'Pending') {
    header("Location: /src/views/reservations/view.php?id=" . $reservationId);
    exit();
}

// Handle form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remarks = $_POST['remarks'] ?? '';

    if (empty($remarks)) {
        $error = "Please provide a reason for canceling the reservation.";
    } else {
        // Get cancelled status ID
        $statusQuery = "SELECT status_id FROM reservation_status WHERE status_name = 'Cancelled'";
        $statusResult = mysqli_query($conn, $statusQuery);
        $status = mysqli_fetch_assoc($statusResult);

        if ($status) {
            // Update reservation status
            $updateQuery = "UPDATE reservation SET status_id = ?, remarks = ? WHERE reservation_id = ?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "isi", $status['status_id'], $remarks, $reservationId);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $success = true;
                // Redirect to my reservations page after 2 seconds
                header("refresh:2;url=/src/views/reservations/my-reservations.php");

                // Notify user and admins of cancellation
                require_once __DIR__ . '/../../controllers/NotificationController.php';
                $notificationController = new \Controllers\NotificationController();
                $title = 'Reservation Cancelled';
                $message = 'Reservation (ID: ' . $reservationId . ') for vehicle ' . $reservation['type_of_vehicle'] . ' (' . $reservation['plate_no'] . ') has been cancelled.';
                $notificationController->create($reservation['user_id'], $title, $message, 'warning');
                $notificationController->notifyAdmins($title, $message, 'warning');
            } else {
                $error = "Failed to cancel reservation.";
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
    <h1>Cancel Reservation</h1>
    <div class="header-actions">
        <a href="/src/views/reservations/view.php?id=<?php echo $reservationId; ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Reservation
        </a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Reservation cancelled successfully! Redirecting...
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
                <h5 class="card-title mb-0">Reservation Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Reservation ID</label>
                        <p><?php echo $reservation['reservation_id']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <p>
                            <span class="badge bg-warning">
                                <?php echo $reservation['reservation_status']; ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date of Use</label>
                        <p><?php echo date('F d, Y', strtotime($reservation['date_of_use'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Time</label>
                        <p>
                            <?php echo date('h:i A', strtotime($reservation['departure_time'])); ?> - 
                            <?php echo date('h:i A', strtotime($reservation['return_time'])); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Departure Area</label>
                        <p><?php echo $reservation['departure_area']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Destination</label>
                        <p><?php echo $reservation['destination']; ?></p>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Purpose</label>
                        <p><?php echo nl2br($reservation['purpose']); ?></p>
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
                    <label class="form-label fw-bold">Vehicle Type</label>
                    <p><?php echo $reservation['type_of_vehicle']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Plate Number</label>
                    <p><?php echo $reservation['plate_no']; ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Capacity</label>
                    <p><?php echo $reservation['capacity']; ?> passengers</p>
                </div>
            </div>
        </div>

        <!-- Cancel Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Cancel Reservation</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Reason for Cancellation</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="3" required
                                  placeholder="Please provide a reason for canceling this reservation..."></textarea>
                        <div class="invalid-feedback">Please provide a reason for canceling the reservation.</div>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fa fa-times"></i> Cancel Reservation
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