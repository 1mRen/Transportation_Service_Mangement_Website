<?php
/**
 * Delete Driver
 * 
 * This script handles the deletion of a driver and includes a confirmation page
 */
// Require controller
require_once __DIR__ . '/../../controllers/DriverController.php';

// Initialize session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize controller
$driverController = new DriverController();

// Get driver ID from URL
$driverId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to drivers list
if ($driverId === 0) {
    $_SESSION['error_message'] = "Invalid driver ID!";
    header('Location: index.php');
    exit;
}

// Get driver details
$driver = $driverController->getDriverById($driverId);

// If driver not found, redirect to drivers list
if (!$driver) {
    $_SESSION['error_message'] = "Driver not found!";
    header('Location: index.php');
    exit;
}

// Check if the driver has any assignments
$driverAssignments = $driverController->getDriverAssignments($driverId);
$hasAssignments = !empty($driverAssignments);

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Attempt to delete the driver
    $result = $driverController->deleteDriver($driverId);
    
    if ($result) {
        $_SESSION['success_message'] = "Driver deleted successfully!";
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['error_message'] = "Unable to delete driver. The driver may be assigned to a vehicle.";
        // Redirect back to the delete page
        header('Location: delete.php?id=' . $driverId);
        exit;
    }
}

// Page title
$pageTitle = "Delete Driver: " . $driver['full_name'];

// Include header and create layout instance
include_once __DIR__ . '/../../views/layout/layout.php';
$layout = new Layout($pageTitle, 'drivers');
$layout->renderHeader();
?>

<div class="container-fluid">
    <h1 class="mt-4">Delete Driver</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Drivers</a></li>
        <li class="breadcrumb-item active">Delete Driver</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-times me-1"></i> Confirm Driver Deletion
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <div class="alert alert-warning">
                <h4><i class="fas fa-exclamation-triangle me-2"></i> Warning</h4>
                <p>You are about to delete the following driver:</p>
                <div class="row mt-3">
                    <div class="col-md-2">
                        <?php if (!empty($driver['profile_pic_url'])): ?>
                            <img src="../../public/<?= htmlspecialchars($driver['profile_pic_url']) ?>" 
                                 alt="Driver Profile" class="img-thumbnail" style="max-width: 100px;">
                        <?php else: ?>
                            <div class="text-center p-3 bg-light">
                                <i class="fas fa-user fa-3x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-10">
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">Name</th>
                                <td><?= htmlspecialchars($driver['full_name']) ?></td>
                            </tr>
                            <tr>
                                <th>License</th>
                                <td><?= htmlspecialchars($driver['driver_license_no']) ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge bg-<?= $driver['status_id'] == 1 ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($driver['status_name']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php if ($hasAssignments): ?>
                <div class="alert alert-danger">
                    <h5><i class="fas fa-ban me-2"></i> Cannot Delete Driver</h5>
                    <p>This driver cannot be deleted because they are currently assigned to vehicles:</p>
                    <ul>
                        <?php foreach ($driverAssignments as $assignment): ?>
                            <li>
                                Vehicle: <?= htmlspecialchars($assignment['type_of_vehicle']) ?> 
                                (<?= htmlspecialchars($assignment['plate_no']) ?>)
                                - From: <?= date('M d, Y', strtotime($assignment['assigned_date'])) ?>
                                <?php if ($assignment['end_date']): ?>
                                    to <?= date('M d, Y', strtotime($assignment['end_date'])) ?>
                                <?php else: ?>
                                    (Ongoing)
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="mb-0">
                        Please end or reassign all vehicle assignments before deleting this driver.
                    </p>
                </div>
            <?php else: ?>
                <form action="delete.php?id=<?= $driverId ?>" method="POST">
                    <div class="alert alert-danger">
                        <p><strong>This action cannot be undone.</strong> Are you sure you want to delete this driver?</p>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Yes, Delete Driver
                        </button>
                        <a href="index.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <a href="view.php?id=<?= $driverId ?>" class="btn btn-info ms-2">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Render the footer
$layout->renderFooter();
?>