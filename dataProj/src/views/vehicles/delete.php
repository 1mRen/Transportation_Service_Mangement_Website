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

// Create database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Initialize VehicleController
$vehicleController = new \Controllers\VehicleController($conn);

// Handle POST request for direct deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vehicle_id'])) {
    $vehicleId = (int)$_POST['vehicle_id'];
    
    // Delete vehicle
    $result = $vehicleController->deleteVehicle($vehicleId);
    
    // Redirect with success or error message
    if ($result['success']) {
        header("Location: /src/views/vehicles/vehicles.php?deleted=true");
    } else {
        header("Location: /src/views/vehicles/vehicles.php?error=" . urlencode($result['message']));
    }
    exit();
}

// If not a POST request or no vehicle_id, handle as a GET request for confirmation page
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /src/views/vehicles/vehicles.php");
    exit();
}

$vehicleId = (int)$_GET['id'];

// Get vehicle details
$vehicle = $vehicleController->getVehicleById($vehicleId);

// If vehicle not found, redirect to vehicles list
if (!$vehicle) {
    header("Location: /src/views/vehicles/vehicles.php");
    exit();
}

// Create a new Layout instance
$layout = new Layout('Delete Vehicle: ' . $vehicle['plate_no'], 'vehicles');

// Render the header
$layout->renderHeader();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Delete Vehicle</h1>
        <a href="/src/views/vehicles/view.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Details
        </a>
    </div>
    
    <!-- Delete Vehicle Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">Confirm Deletion</h6>
        </div>
        <div class="card-body">
            <div class="alert alert-warning" role="alert">
                <i class="fa fa-exclamation-triangle"></i> Are you sure you want to delete this vehicle? This action cannot be undone.
            </div>
            
            <div class="vehicle-info mb-4">
                <h5 class="font-weight-bold">Vehicle Details:</h5>
                <p><strong>Vehicle ID:</strong> <?php echo $vehicle['vehicle_id']; ?></p>
                <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_no']); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($vehicle['type_of_vehicle']); ?></p>
                <p><strong>Capacity:</strong> <?php echo $vehicle['capacity']; ?> passengers</p>
                <p>
                    <strong>Status:</strong> 
                    <span class="badge bg-<?php echo strtolower($vehicle['status_name']) === 'available' ? 'success' : (strtolower($vehicle['status_name']) === 'maintenance' ? 'warning' : 'secondary'); ?>">
                        <?php echo htmlspecialchars($vehicle['status_name']); ?>
                    </span>
                </p>
            </div>
            
            <form action="/src/views/vehicles/delete.php" method="POST" class="text-center">
                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['vehicle_id']; ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fa fa-trash"></i> Confirm Delete
                </button>
                <a href="/src/views/vehicles/view.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="btn btn-secondary">
                    <i class="fa fa-times"></i> Cancel
                </a>
            </form>
        </div>
    </div>
</div>

<?php
// Render the footer
$layout->renderFooter();
?>