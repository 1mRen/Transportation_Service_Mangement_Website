<?php
/**
 * View Driver Details
 * 
 * Displays detailed information about a specific driver
 */

// Require controller
require_once __DIR__ . '/../../controllers/DriverController.php';

// Initialize controller
$driverController = new DriverController();

// Get driver ID from URL
$driverId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to drivers list
if ($driverId === 0) {
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

// Get driver assignments
$assignments = $driverController->getDriverAssignments($driverId);

// Page title
$pageTitle = "Driver Details: " . $driver['full_name'];

// Include layout file and instantiate Layout class
require_once __DIR__ . '/../../views/layout/layout.php';
$layout = new Layout($pageTitle, 'drivers');

// Render the header
$layout->renderHeader();
?>

<div class="container-fluid">
    <h1 class="mt-4">Driver Details</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/src/views/drivers/index.php">Drivers</a></li>
        <li class="breadcrumb-item active">Driver Details</li>
    </ol>
    
    <div class="row">
        <!-- Driver Information -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-user me-1"></i> Driver Information</div>
                        <div>
                            <a href="/src/views/drivers/edit.php?id=<?= $driver['driver_id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php if (!empty($driver['profile_pic_url'])): ?>
                            <img src="/public/<?= $driver['profile_pic_url'] ?>" 
                                 alt="<?= $driver['full_name'] ?>" 
                                 class="img-fluid rounded-circle" 
                                 style="max-height: 200px;">
                        <?php else: ?>
                            <img src="/public/assets/img/default-user.png" 
                                 alt="Default Profile" 
                                 class="img-fluid rounded-circle" 
                                 style="max-height: 200px;">
                        <?php endif; ?>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>ID:</strong> <?= $driver['driver_id'] ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Full Name:</strong> <?= $driver['full_name'] ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Age:</strong> <?= $driver['age'] ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Driver License:</strong> <?= $driver['driver_license_no'] ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Contact Number:</strong> <?= $driver['contact_no'] ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Status:</strong>
                            <?php
                            $statusClass = 'secondary';
                            if ($driver['status_name'] == 'Active') $statusClass = 'success';
                            if ($driver['status_name'] == 'On Leave') $statusClass = 'warning';
                            if ($driver['status_name'] == 'Retired') $statusClass = 'danger';
                            ?>
                            <span class="badge bg-<?= $statusClass ?>"><?= $driver['status_name'] ?></span>
                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="/src/views/drivers/index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <a href="/src/views/drivers/status.php?id=<?= $driver['driver_id'] ?>" class="btn btn-warning">
                            <i class="fas fa-exchange-alt"></i> Update Status
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Driver Assignments -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-clipboard-list me-1"></i> Vehicle Assignments
                </div>
                <div class="card-body">
                    <?php if (empty($assignments)): ?>
                        <div class="alert alert-info">
                            No vehicle assignments found for this driver.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Vehicle</th>
                                        <th>Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td><?= $assignment['assignment_id'] ?></td>
                                            <td><?= $assignment['plate_no'] ?></td>
                                            <td><?= $assignment['type_of_vehicle'] ?></td>
                                            <td><?= date('Y-m-d', strtotime($assignment['assigned_date'])) ?></td>
                                            <td>
                                                <?= $assignment['end_date'] ? date('Y-m-d', strtotime($assignment['end_date'])) : 'Ongoing' ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                if ($assignment['assignment_status'] == 'Active') $statusClass = 'success';
                                                if ($assignment['assignment_status'] == 'Completed') $statusClass = 'info';
                                                if ($assignment['assignment_status'] == 'Cancelled') $statusClass = 'danger';
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>"><?= $assignment['assignment_status'] ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Render the footer
$layout->renderFooter();
?>