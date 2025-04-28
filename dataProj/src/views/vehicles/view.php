<?php
// Import the layout class and controller
require_once __DIR__ . '/../../views/layout/layout.php';
require_once __DIR__ . '/../../controllers/VehicleController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: /src/views/auth/signin.php");
    exit();
}

// Check if vehicle ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /src/views/vehicles/vehicles.php");
    exit();
}

$vehicleId = (int)$_GET['id'];

// Create database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Initialize VehicleController
$vehicleController = new \Controllers\VehicleController($conn);

// Get vehicle details
$vehicle = $vehicleController->getVehicleById($vehicleId);

// If vehicle not found, redirect to vehicles list
if (!$vehicle) {
    header("Location: /src/views/vehicles/vehicles.php");
    exit();
}

// Get vehicle assignments
$assignments = $vehicleController->getVehicleAssignments($vehicleId);

// Get vehicle reservations
$reservations = $vehicleController->getVehicleReservations($vehicleId);

// Create a new Layout instance
$layout = new Layout('Vehicle Details: ' . $vehicle['plate_no'], 'vehicles');

// Render the header
$layout->renderHeader();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Vehicle Details</h1>
        <div>
            <a href="/src/views/vehicles/vehicles.php" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
            <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
            <a href="/src/views/vehicles/edit.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-primary">
                <i class="fa fa-edit"></i> Edit Vehicle
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vehicle Information Card -->
    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Vehicle Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 15rem;" 
                             src="/public/assets/img/<?php echo strtolower($vehicle['type_of_vehicle']) === 'bus' ? 'bus.jpg' : 'modern-bus.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($vehicle['type_of_vehicle']); ?>">
                    </div>
                    <div class="vehicle-info">
                        <p><strong>Vehicle ID:</strong> <?php echo $vehicle['vehicle_id']; ?></p>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($vehicle['type_of_vehicle']); ?></p>
                        <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_no']); ?></p>
                        <p><strong>Capacity:</strong> <?php echo $vehicle['capacity']; ?> passengers</p>
                        <p>
                            <strong>Status:</strong> 
                            <span class="badge bg-<?php echo strtolower($vehicle['status_name']) === 'available' ? 'success' : (strtolower($vehicle['status_name']) === 'maintenance' ? 'warning' : 'secondary'); ?>">
                                <?php echo htmlspecialchars($vehicle['status_name']); ?>
                            </span>
                        </p>
                    </div>
                    
                    <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
                    <hr>
                    <div class="text-center">
                        <a href="/src/views/vehicles/status.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fa fa-cog"></i> Change Status
                        </a>
                        <a href="/src/views/vehicles/assign.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-success btn-sm">
                            <i class="fa fa-user-plus"></i> Assign Driver
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-xl-8 col-lg-7">
            <!-- Current Assignment Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Assignment</h6>
                </div>
                <div class="card-body">
                    <?php 
                    $currentAssignment = null;
                    foreach ($assignments as $assignment) {
                        if ($assignment['end_date'] === null) {
                            $currentAssignment = $assignment;
                            break;
                        }
                    }
                    
                    if ($currentAssignment): 
                    ?>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Driver:</strong> <?php echo htmlspecialchars($currentAssignment['driver_name']); ?></p>
                            <p><strong>Assigned Date:</strong> <?php echo date('M d, Y', strtotime($currentAssignment['assigned_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span class="badge bg-success"><?php echo htmlspecialchars($currentAssignment['status_name']); ?></span>
                            </p>
                            <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
                            <form action="/src/views/vehicles/end_assignment.php" method="POST" class="mt-3">
                                <input type="hidden" name="assignment_id" value="<?php echo $currentAssignment['assignment_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-times-circle"></i> End Assignment
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-3">
                        <p class="text-muted mb-0">No active driver assignment.</p>
                        <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
                        <a href="/src/views/vehicles/assign.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-success btn-sm mt-3">
                            <i class="fa fa-user-plus"></i> Assign Driver
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Assignment History Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Assignment History</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($assignments)): ?>
                    <div class="text-center py-3">
                        <p class="text-muted mb-0">No assignment history available.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Driver</th>
                                    <th>Assigned Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><?php echo $assignment['assignment_id']; ?></td>
                                    <td><?php echo htmlspecialchars($assignment['driver_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($assignment['assigned_date'])); ?></td>
                                    <td>
                                        <?php echo $assignment['end_date'] ? date('M d, Y', strtotime($assignment['end_date'])) : 'Active'; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $assignment['end_date'] ? 'secondary' : 'success'; ?>">
                                            <?php echo htmlspecialchars($assignment['status_name']); ?>
                                        </span>
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
    
    <!-- Reservation History Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Reservation History</h6>
        </div>
        <div class="card-body">
            <?php if (empty($reservations)): ?>
            <div class="text-center py-3">
                <p class="text-muted mb-0">No reservation history available.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Applicant</th>
                            <th>Date of Use</th>
                            <th>Destination</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo $reservation['reservation_id']; ?></td>
                            <td><?php echo htmlspecialchars($reservation['applicant_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($reservation['date_of_use'])); ?></td>
                            <td><?php echo htmlspecialchars($reservation['destination']); ?></td>
                            <td>
                                <?php echo date('h:i A', strtotime($reservation['departure_time'])); ?> - 
                                <?php echo date('h:i A', strtotime($reservation['return_time'])); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo strtolower($reservation['status_name']) === 'pending' ? 'warning' : (strtolower($reservation['status_name']) === 'approved' ? 'success' : (strtolower($reservation['status_name']) === 'completed' ? 'info' : 'danger')); ?>">
                                    <?php echo htmlspecialchars($reservation['status_name']); ?>
                                </span>
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

<?php
// Render the footer
$layout->renderFooter();
?>