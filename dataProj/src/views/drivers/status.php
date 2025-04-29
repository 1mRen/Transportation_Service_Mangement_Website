<?php
/**
 * Update Driver Status
 * 
 * This script handles the updating of a driver's status (active, on leave, retired, etc.)
 */
// Require controller
require_once __DIR__ . '/../../controllers/DriverController.php';

// Initialize session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['id']) || strtolower($_SESSION['role']) !== 'admin') {
    $_SESSION['error_message'] = "You don't have permission to access this page!";
    header('Location: /index.php');
    exit;
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

// Get all driver statuses for the form dropdown
$statuses = $driverController->getAllDriverStatuses();

// Get driver's assignments to check if status change is allowed
$driverAssignments = $driverController->getDriverAssignments($driverId);
$hasActiveAssignments = false;

// Check if driver has any active assignments
foreach ($driverAssignments as $assignment) {
    if ($assignment['status_id'] == 1) { // Assuming status_id 1 is "Active"
        $hasActiveAssignments = true;
        break;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatusId = intval($_POST['status_id']);
    
    // Validate if the status exists
    $validStatus = false;
    foreach ($statuses as $status) {
        if ($status['status_id'] == $newStatusId) {
            $validStatus = true;
            break;
        }
    }
    
    if (!$validStatus) {
        $_SESSION['error_message'] = "Invalid status selected!";
    } else {
        // If driver has active assignments and being set to inactive status, show warning
        $isDeactivating = ($driver['status_id'] == 1 && $newStatusId != 1);
        
        if ($isDeactivating && $hasActiveAssignments && !isset($_POST['force_update'])) {
            $_SESSION['warning_message'] = "This driver has active assignments. Please confirm status change.";
            $_SESSION['force_update'] = true;
        } else {
            // Attempt to update the driver status
            $result = $driverController->updateDriverStatus($driverId, $newStatusId);
            
            if ($result) {
                $_SESSION['success_message'] = "Driver status updated successfully!";
                
                // If driver was deactivated with active assignments, make a note
                if ($isDeactivating && $hasActiveAssignments) {
                    $_SESSION['warning_message'] = "Driver status updated but they still have active assignments. Please reassign these vehicles.";
                }
                
                header('Location: view.php?id=' . $driverId);
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to update driver status!";
            }
        }
    }
}

// Page title
$pageTitle = "Update Status: " . $driver['full_name'];

// Include layout file and instantiate Layout class
require_once __DIR__ . '/../../views/layout/layout.php';
$layout = new Layout($pageTitle, 'drivers');

// Render the header
$layout->renderHeader();
?>

<div class="container-fluid">
    <h1 class="mt-4">Update Driver Status</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Drivers</a></li>
        <li class="breadcrumb-item"><a href="view.php?id=<?= $driverId ?>">Driver Details</a></li>
        <li class="breadcrumb-item active">Update Status</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-edit me-1"></i> Change Driver Status
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['warning_message'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <?= $_SESSION['warning_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['warning_message']); ?>
            <?php endif; ?>
            
            <div class="row mb-4">
                <div class="col-md-2">
                    <?php if (!empty($driver['profile_pic_url'])): ?>
                        <img src="../../public/<?= htmlspecialchars($driver['profile_pic_url']) ?>" 
                             alt="Driver Profile" class="img-thumbnail" style="max-width: 150px;">
                    <?php else: ?>
                        <div class="text-center p-4 bg-light">
                            <i class="fas fa-user fa-4x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-10">
                    <h3><?= htmlspecialchars($driver['full_name']) ?></h3>
                    <p class="text-muted">
                        <strong>License:</strong> <?= htmlspecialchars($driver['driver_license_no']) ?> | 
                        <strong>Contact:</strong> <?= htmlspecialchars($driver['contact_no']) ?> | 
                        <strong>Current Status:</strong> 
                        <span class="badge 
                            <?php
                                // Add appropriate color based on status
                                switch(strtolower($driver['status_name'])) {
                                    case 'active':
                                        echo 'bg-success';
                                        break;
                                    case 'on leave':
                                        echo 'bg-warning';
                                        break;
                                    case 'retired':
                                    case 'suspended':
                                        echo 'bg-danger';
                                        break;
                                    default:
                                        echo 'bg-secondary';
                                }
                            ?>">
                            <?= htmlspecialchars($driver['status_name']) ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <?php if ($hasActiveAssignments): ?>
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle me-2"></i> Active Assignments</h5>
                    <p>This driver currently has active vehicle assignments. Changing their status may affect these assignments.</p>
                    <table class="table table-sm table-bordered mt-2">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Plate Number</th>
                                <th>Assigned Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($driverAssignments as $assignment): ?>
                                <?php if ($assignment['status_id'] == 1): // Only show active assignments ?>
                                    <tr>
                                        <td><?= htmlspecialchars($assignment['type_of_vehicle']) ?></td>
                                        <td><?= htmlspecialchars($assignment['plate_no']) ?></td>
                                        <td><?= date('M d, Y', strtotime($assignment['assigned_date'])) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row mb-3">
                    <label for="status_id" class="col-sm-2 col-form-label">New Status:</label>
                    <div class="col-sm-6">
                        <select name="status_id" id="status_id" class="form-select" required>
                            <option value="">-- Select Status --</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= $status['status_id'] ?>" 
                                    <?= ($status['status_id'] == $driver['status_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($status['status_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['force_update']) && $_SESSION['force_update']): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="force_update" name="force_update" value="1">
                        <label class="form-check-label text-danger" for="force_update">
                            I understand this driver has active assignments and wish to change their status anyway.
                        </label>
                    </div>
                    <?php unset($_SESSION['force_update']); ?>
                <?php endif; ?>
                
                <div class="mt-4">
                    <button type="submit" name="update_status" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Status
                    </button>
                    <a href="view.php?id=<?= $driverId ?>" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Render the footer
$layout->renderFooter();
?>