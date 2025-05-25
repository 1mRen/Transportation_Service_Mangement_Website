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

// Get all vehicle statuses for dropdown
$vehicleStatuses = $vehicleController->getAllVehicleStatuses();

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $plateNo = trim($_POST['plate_no'] ?? '');
    $type = trim($_POST['type_of_vehicle'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    $statusId = (int)($_POST['status_id'] ?? 1);
    
    // Validate plate number
    if (empty($plateNo)) {
        $errors['plate_no'] = 'Plate number is required';
    }
    
    // Validate vehicle type
    if (empty($type)) {
        $errors['type_of_vehicle'] = 'Vehicle type is required';
    }
    
    // Validate capacity
    if ($capacity <= 0) {
        $errors['capacity'] = 'Capacity must be greater than 0';
    }
    
    // If no errors, create vehicle
    if (empty($errors)) {
        $result = $vehicleController->createVehicle($plateNo, $type, $capacity, $statusId);
        
        if ($result['success']) {
            // Redirect to vehicle details page
            header("Location: /src/views/vehicles/view.php?id=" . $result['vehicle_id']);
            exit();
        } else {
            $errors['general'] = $result['message'];
        }
    }
}

// Create a new Layout instance
$layout = new Layout('Create New Vehicle', 'vehicles');

// Render the header
$layout->renderHeader();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create New Vehicle</h1>
        <a href="/src/views/vehicles/vehicles.php" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>

    <!-- Create Vehicle Form Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Vehicle Information</h6>
        </div>
        <div class="card-body">
            <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
            <?php endif; ?>

            <form action="/src/views/vehicles/create.php" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="plate_no" class="form-label">Plate Number</label>
                            <input type="text" class="form-control <?php echo isset($errors['plate_no']) ? 'is-invalid' : ''; ?>" 
                                id="plate_no" name="plate_no" value="<?php echo htmlspecialchars($_POST['plate_no'] ?? ''); ?>" required>
                            <?php if (isset($errors['plate_no'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['plate_no']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="type_of_vehicle" class="form-label">Vehicle Type</label>
                            <select class="form-control <?php echo isset($errors['type_of_vehicle']) ? 'is-invalid' : ''; ?>" 
                                id="type_of_vehicle" name="type_of_vehicle" required>
                                <option value="">Select Type</option>
                                <option value="Bus" <?php echo (isset($_POST['type_of_vehicle']) && $_POST['type_of_vehicle'] === 'Bus') ? 'selected' : ''; ?>>Bus</option>
                                <option value="Coaster" <?php echo (isset($_POST['type_of_vehicle']) && $_POST['type_of_vehicle'] === 'Coaster') ? 'selected' : ''; ?>>Coaster</option>
                                <option value="Canter" <?php echo (isset($_POST['type_of_vehicle']) && $_POST['type_of_vehicle'] === 'Canter') ? 'selected' : ''; ?>>Canter</option>
                                <option value="Private Vehicle" <?php echo (isset($_POST['type_of_vehicle']) && $_POST['type_of_vehicle'] === 'Private Vehicle') ? 'selected' : ''; ?>>Private Vehicle</option>
                            </select>
                            <?php if (isset($errors['type_of_vehicle'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['type_of_vehicle']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity (Passengers)</label>
                            <input type="number" class="form-control <?php echo isset($errors['capacity']) ? 'is-invalid' : ''; ?>" 
                                id="capacity" name="capacity" min="1" value="<?php echo htmlspecialchars($_POST['capacity'] ?? ''); ?>" required>
                            <?php if (isset($errors['capacity'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['capacity']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status_id" class="form-label">Status</label>
                            <select class="form-control" id="status_id" name="status_id" required>
                                <?php foreach ($vehicleStatuses as $status): ?>
                                <option value="<?php echo $status['status_id']; ?>" 
                                    <?php echo (isset($_POST['status_id']) && (int)$_POST['status_id'] === $status['status_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status['status_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Create Vehicle
                    </button>
                    <a href="/src/views/vehicles/vehicles.php" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
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