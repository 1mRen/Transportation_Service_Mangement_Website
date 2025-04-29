<?php
/**
 * Edit Driver
 * 
 * Form to edit existing driver information
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
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare driver data
    $driverData = [
        'full_name' => $_POST['full_name'] ?? '',
        'age' => $_POST['age'] ?? 0,
        'driver_license_no' => $_POST['driver_license_no'] ?? '',
        'contact_no' => $_POST['contact_no'] ?? '',
        'status_id' => $_POST['status_id'] ?? 1,
        'profile_pic_url' => $driver['profile_pic_url'] ?? ''
    ];
    
    // Validate input
    $errors = [];
    
    if (empty($driverData['full_name'])) {
        $errors[] = "Full name is required";
    }
    
    if (empty($driverData['age']) || !is_numeric($driverData['age'])) {
        $errors[] = "Valid age is required";
    }
    
    if (empty($driverData['driver_license_no'])) {
        $errors[] = "Driver license number is required";
    } elseif ($driverData['driver_license_no'] !== $driver['driver_license_no'] && 
              $driverController->driverLicenseExists($driverData['driver_license_no'], $driverId)) {
        $errors[] = "Driver license number already exists";
    }
    
    if (empty($driverData['contact_no'])) {
        $errors[] = "Contact number is required";
    }
    
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = $driverController->uploadProfilePicture($_FILES['profile_pic']);
        if ($uploadResult) {
            $driverData['profile_pic_url'] = $uploadResult;
        } else {
            $errors[] = "Failed to upload profile picture. Only JPG, JPEG and PNG files are allowed.";
        }
    }
    
    // If no errors, update driver
    if (empty($errors)) {
        $result = $driverController->updateDriver($driverId, $driverData);
        
        if ($result) {
            $_SESSION['success_message'] = "Driver updated successfully!";
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['error_message'] = "Failed to update driver. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}
// Get all driver statuses
$driverStatuses = $driverController->getAllDriverStatuses();
// Page title
$pageTitle = "Edit Driver: " . $driver['full_name'];

// Include layout file and instantiate Layout class
require_once __DIR__ . '/../../views/layout/layout.php';
$layout = new Layout($pageTitle, 'drivers');

// Render the header
$layout->renderHeader();
?>

<div class="container-fluid">
    <h1 class="mt-4">Edit Driver</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Drivers</a></li>
        <li class="breadcrumb-item active">Edit Driver</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-edit me-1"></i> Edit Driver Information
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <form action="edit.php?id=<?= $driverId ?>" method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required 
                                   value="<?= htmlspecialchars($driver['full_name']) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="age" name="age" required min="18" max="65"
                                   value="<?= $driver['age'] ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="driver_license_no" class="form-label">Driver License Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="driver_license_no" name="driver_license_no" required
                                   value="<?= htmlspecialchars($driver['driver_license_no']) ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="contact_no" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_no" name="contact_no" required
                                   value="<?= htmlspecialchars($driver['contact_no']) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="status_id" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status_id" name="status_id" required>
                                <?php foreach ($driverStatuses as $status): ?>
                                    <option value="<?= $status['status_id'] ?>" 
                                            <?= ($driver['status_id'] == $status['status_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['status_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="profile_pic" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/jpeg,image/png,image/jpg">
                            <small class="text-muted">Leave empty to keep current profile picture</small>
                        </div>
                        
                        <?php if (!empty($driver['profile_pic_url'])): ?>
                            <div class="mb-3">
                                <label class="form-label">Current Profile Picture</label>
                                <div>
                                    <img src="../../public/<?= htmlspecialchars($driver['profile_pic_url']) ?>" 
                                         alt="Driver Profile" class="img-thumbnail" style="max-width: 150px;">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                        <a href="index.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <a href="view.php?id=<?= $driverId ?>" class="btn btn-info ms-2">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Render the footer
$layout->renderFooter();
?>