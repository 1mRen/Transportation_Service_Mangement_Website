<?php
/**
 * Driver List View
 * 
 * Displays a list of all drivers with their status information
 */

// Import the layout class
require_once __DIR__ . '/../../views/layout/layout.php';
require_once __DIR__ . '/../../controllers/DriverController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: /src/views/auth/signin.php");
    exit();
}

// Initialize controller
$driverController = new DriverController();

// Get all drivers
$drivers = $driverController->getAllDrivers();

// Get driver statistics
$driverStats = $driverController->getDriverStatistics();

// Create a new Layout instance
$layout = new Layout('Drivers Management', 'drivers');

// Render the header
$layout->renderHeader();
?>

<div class="container-fluid">
    <h1 class="mt-4">Drivers Management</h1>
    <ol class="breadcrumb mb-4">
    </ol>
    
    <!-- Driver Statistics -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Total Drivers</div>
                        <div class="h3"><?= $driverStats['total'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Active Drivers</div>
                        <div class="h3"><?= $driverStats['active'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>On Leave</div>
                        <div class="h3"><?= $driverStats['on_leave'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>Currently Assigned</div>
                        <div class="h3"><?= $driverStats['assigned'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Drivers List -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div><i class="fas fa-users me-1"></i> Drivers List</div>
                <a href="/src/views/drivers/create.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Driver
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <table id="driversTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>License No.</th>
                        <th>Contact No.</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($drivers as $driver): ?>
                        <tr>
                            <td><?= $driver['driver_id'] ?></td>
                            <td>
                                <?php if (!empty($driver['profile_pic_url'])): ?>
                                    <img src="/public/<?= $driver['profile_pic_url'] ?>" alt="<?= htmlspecialchars($driver['full_name']) ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;margin-right:6px;">
                                <?php else: ?>
                                    <img src="/public/assets/img/default-user.png" alt="Default Profile" style="width:32px;height:32px;border-radius:50%;object-fit:cover;margin-right:6px;">
                                <?php endif; ?>
                                <?= htmlspecialchars($driver['full_name']) ?>
                            </td>
                            <td><?= $driver['age'] ?></td>
                            <td><?= $driver['driver_license_no'] ?></td>
                            <td><?= $driver['contact_no'] ?></td>
                            <td>
                                <?php
                                $statusClass = 'secondary';
                                if ($driver['status_name'] == 'Active') $statusClass = 'success';
                                if ($driver['status_name'] == 'On Leave') $statusClass = 'warning';
                                if ($driver['status_name'] == 'Retired') $statusClass = 'danger';
                                ?>
                                <span class="badge bg-<?= $statusClass ?>"><?= $driver['status_name'] ?></span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/src/views/drivers/view.php?id=<?= $driver['driver_id'] ?>" class="btn btn-info btn-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/src/views/drivers/edit.php?id=<?= $driver['driver_id'] ?>" class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/src/views/drivers/status.php?id=<?= $driver['driver_id'] ?>" class="btn btn-warning btn-sm" title="Update Status">
                                        <i class="fas fa-exchange-alt"></i>
                                    </a>
                                    <a href="/src/views/drivers/delete.php?id=<?= $driver['driver_id'] ?>" class="btn btn-danger btn-sm" title="Delete" 
                                       onclick="return confirm('Are you sure you want to delete this driver?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#driversTable').DataTable({
            responsive: true
        });
    });
</script>

<?php
// Render the footer
$layout->renderFooter();
?>