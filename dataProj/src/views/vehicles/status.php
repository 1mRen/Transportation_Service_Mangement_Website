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

// Check if user has admin role
if (strtolower($_SESSION['role']) !== 'admin') {
    header("Location: /src/views/vehicles/vehicles.php");
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

// Get all vehicle statuses
$vehicleStatuses = $vehicleController->getAllVehicleStatuses();

// Handle form submission
$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (isset($_POST['status_id']) && is_numeric($_POST['status_id'])) {
        $statusId = (int)$_POST['status_id'];
        
        // Update vehicle status
        $result = $vehicleController->updateVehicleStatus($vehicleId, $statusId);
        
        if ($result) {
            $success = true;
            // Refresh vehicle details
            $vehicle = $vehicleController->getVehicleById($vehicleId);
        } else {
            $error = "Failed to update vehicle status.";
        }
    } else {
        $error = "Invalid status selected.";
    }
}

// Create a new Layout instance
$layout = new Layout('Update Vehicle Status: ' . $vehicle['plate_no'], 'vehicles');

// Render the header
$layout->renderHeader();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Update Vehicle Status</h1>
        <div>
            <a href="/src/views/vehicles/view.php?id=<?php echo $vehicleId; ?>" class="btn btn-info">
                <i class="fa fa-eye"></i> View Details
            </a>
            <a href="/src/views/vehicles/vehicles.php" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Vehicle Status Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Vehicle Status Management</h6>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle"></i> Vehicle status updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="font-weight-bold">Vehicle Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="vehicle-info">
                                <p><strong>Vehicle ID:</strong> <?php echo $vehicle['vehicle_id']; ?></p>
                                <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_no']); ?></p>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($vehicle['type_of_vehicle']); ?></p>
                                <p><strong>Capacity:</strong> <?php echo $vehicle['capacity']; ?> passengers</p>
                                <p>
                                    <strong>Current Status:</strong> 
                                    <span class="badge bg-<?php echo strtolower($vehicle['status_name']) === 'available' ? 'success' : (strtolower($vehicle['status_name']) === 'maintenance' ? 'warning' : 'secondary'); ?>">
                                        <?php echo htmlspecialchars($vehicle['status_name']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="font-weight-bold">Update Status</h6>
                        </div>
                        <div class="card-body">
                            <form action="/src/views/vehicles/status.php?id=<?php echo $vehicleId; ?>" method="POST">
                                <div class="mb-3">
                                    <label for="status_id" class="form-label">New Status</label>
                                    <select class="form-select" id="status_id" name="status_id" required>
                                        <?php foreach ($vehicleStatuses as $status): ?>
                                        <option value="<?php echo $status['status_id']; ?>" <?php echo $status['status_id'] == $vehicle['status_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($status['status_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">
                                        Select the new status for this vehicle.
                                    </small>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> Note: Changing vehicle status may affect its availability for assignments and reservations.
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Update Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Status History Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="font-weight-bold">Status Change Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>Available:</strong> Vehicle is ready for new assignments or reservations.
                        </li>
                        <li class="list-group-item">
                            <strong>In Use:</strong> Vehicle is currently assigned to a driver or trip.
                        </li>
                        <li class="list-group-item">
                            <strong>Maintenance:</strong> Vehicle is undergoing maintenance and cannot be assigned.
                        </li>
                        <li class="list-group-item">
                            <strong>Out of Service:</strong> Vehicle is not operational for an extended period.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Render the footer
$layout->renderFooter();
?>