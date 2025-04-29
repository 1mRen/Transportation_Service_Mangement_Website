<?php
// Import the layout class and controllers
require_once __DIR__ . '/../layout/layout.php';
require_once __DIR__ . '/../../controllers/VehicleController.php';
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

// Check if user has proper role (admin or staff)
if (strtolower($_SESSION['role']) !== 'admin' && strtolower($_SESSION['role']) !== 'staff') {
    header("Location: /src/views/vehicles/vehicles.php");
    exit();
}

// Create database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Initialize controllers
$vehicleController = new \Controllers\VehicleController($conn);
$driverController = new DriverController();

// Get vehicle ID from URL parameter if provided
$vehicleId = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : null;
$vehicle = null;

// If vehicle ID is provided, get vehicle details
if ($vehicleId) {
    $vehicle = $vehicleController->getVehicleById($vehicleId);
    
    // If vehicle not found or not available, redirect back to vehicles list
    if (!$vehicle || $vehicle['status_id'] != 1) {
        $_SESSION['error'] = "Vehicle not found or is not available for assignment.";
        header("Location: /src/views/vehicles/vehicles.php");
        exit();
    }
}

// Get list of available vehicles if no specific vehicle was requested
$availableVehicles = $vehicleId ? [] : $vehicleController->getAvailableVehicles();

// Get list of active drivers
$today = date('Y-m-d');
$availableDrivers = $driverController->getAvailableDriversOnDate($today);

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $selectedVehicleId = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : $vehicleId;
    $driverId = isset($_POST['driver_id']) ? intval($_POST['driver_id']) : null;
    $assignedDate = isset($_POST['assigned_date']) ? $_POST['assigned_date'] : null;
    $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    
    // Validate required fields
    if (!$selectedVehicleId || !$driverId || !$assignedDate) {
        $error = "All required fields must be filled out.";
    } else {
        // Validate dates
        $currentDate = date('Y-m-d');
        if ($assignedDate < $currentDate) {
            $error = "Assignment date cannot be in the past.";
        } elseif ($endDate && $endDate < $assignedDate) {
            $error = "End date cannot be before the assignment date.";
        } else {
            // Process assignment
            $result = $vehicleController->assignVehicleToDriver($selectedVehicleId, $driverId, $assignedDate, $endDate);
            
            if ($result['success']) {
                $_SESSION['success'] = "Vehicle assigned successfully.";
                header("Location: /src/views/vehicles/view.php?id=" . $selectedVehicleId);
                exit();
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Create a new Layout instance
$layout = new Layout('Assign Vehicle to Driver', 'vehicles');

// Render the header
$layout->renderHeader();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><i class="fas fa-link"></i> Assign Vehicle to Driver</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Vehicle:</label>
                    <div class="col-sm-9">
                        <?php if ($vehicle): ?>
                            <!-- Display selected vehicle info if provided in URL -->
                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['vehicle_id']; ?>">
                            <div class="form-control-plaintext">
                                <strong><?php echo htmlspecialchars($vehicle['plate_no']); ?></strong>
                                <span class="text-muted">(<?php echo htmlspecialchars($vehicle['type_of_vehicle']); ?>, Capacity: <?php echo $vehicle['capacity']; ?>)</span>
                            </div>
                        <?php else: ?>
                            <!-- Vehicle dropdown selection -->
                            <select name="vehicle_id" class="form-select" required>
                                <option value="">-- Select Vehicle --</option>
                                <?php foreach ($availableVehicles as $v): ?>
                                    <option value="<?php echo $v['vehicle_id']; ?>">
                                        <?php echo htmlspecialchars($v['plate_no']); ?> 
                                        (<?php echo htmlspecialchars($v['type_of_vehicle']); ?>, Capacity: <?php echo $v['capacity']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($availableVehicles)): ?>
                                <div class="text-danger mt-2">No available vehicles found.</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Driver:</label>
                    <div class="col-sm-9">
                        <select name="driver_id" class="form-select" required>
                            <option value="">-- Select Driver --</option>
                            <?php foreach ($availableDrivers as $driver): ?>
                                <option value="<?php echo $driver['driver_id']; ?>">
                                    <?php echo htmlspecialchars($driver['full_name']); ?> 
                                    (License: <?php echo htmlspecialchars($driver['driver_license_no']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($availableDrivers)): ?>
                            <div class="text-danger mt-2">No available drivers found for today. Check drivers not currently assigned to other vehicles.</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Assignment Date:</label>
                    <div class="col-sm-9">
                        <input type="date" name="assigned_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">End Date (Optional):</label>
                    <div class="col-sm-9">
                        <input type="date" name="end_date" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                        <small class="text-muted">Leave blank for indefinite assignment</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary" <?php echo (empty($availableDrivers) || (empty($vehicle) && empty($availableVehicles))) ? 'disabled' : ''; ?>>
                            <i class="fas fa-link"></i> Assign Vehicle
                        </button>
                        <a href="/src/views/vehicles/vehicles.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Vehicles
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($vehicle): ?>
    <div class="card shadow mt-4">
        <div class="card-header bg-info text-white">
            <h5><i class="fas fa-info-circle"></i> Assignment History</h5>
        </div>
        <div class="card-body">
            <?php 
            $assignments = $vehicleController->getVehicleAssignments($vehicle['vehicle_id']);
            if (empty($assignments)): 
            ?>
                <p class="text-muted">No previous assignments found for this vehicle.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Driver</th>
                                <th>From Date</th>
                                <th>Until Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($assignment['driver_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($assignment['assigned_date'])); ?></td>
                                <td>
                                    <?php 
                                    echo $assignment['end_date'] 
                                        ? date('M d, Y', strtotime($assignment['end_date'])) 
                                        : '<span class="text-muted">Indefinite</span>'; 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $statusColor = 'secondary';
                                    if ($assignment['status_name'] == 'Active') $statusColor = 'success';
                                    if ($assignment['status_name'] == 'Pending') $statusColor = 'warning';
                                    if ($assignment['status_name'] == 'Completed') $statusColor = 'info';
                                    if ($assignment['status_name'] == 'Cancelled') $statusColor = 'danger';
                                    ?>
                                    <span class="badge bg-<?php echo $statusColor; ?>">
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
    <?php endif; ?>
</div>

<?php
// Render footer
$layout->renderFooter();
?>