<?php
require_once __DIR__ . '/../../controllers/UserManagementController.php';
require_once '../../views/layout/layout.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: /src/views/auth/signin.php");
    exit();
}

$controller = new UserManagementController();
$user = $controller->getUserById($_SESSION['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        // Check file size (2MB max)
        if ($_FILES['profile_pic']['size'] > 2000000) {
            $errors[] = "File is too large. Maximum size is 2MB.";
        } else {
            // Check if file is an actual image
            $check = getimagesize($_FILES['profile_pic']['tmp_name']);
            if ($check === false) {
                $errors[] = "File is not an image.";
            } else {
                // Use the controller's upload method
                $uploadResult = $controller->uploadProfilePicture($_FILES['profile_pic']);
                
                if ($uploadResult) {
                    // If upload successful, update the user's profile
                    if ($controller->updateProfilePicture($_SESSION['id'], $uploadResult)) {
                        $_SESSION['success_message'] = "Profile picture updated successfully!";
                        header("Location: profile.php");
                        exit();
                    } else {
                        $errors[] = "Failed to update profile picture in database.";
                    }
                } else {
                    $errors[] = "Failed to upload profile picture. Only JPG, JPEG, PNG and WEBP files are allowed.";
                }
            }
        }
    }
    
    // Handle other profile updates
    if (isset($_POST['name']) || isset($_POST['email'])) {
        $updateData = [
            'name' => $_POST['name'] ?? $user['name'],
            'email' => $_POST['email'] ?? $user['email'],
            'username' => $user['username'], // Keep existing username
            'role' => $user['role'] // Keep existing role
        ];
        
        if ($controller->update($_SESSION['id'], $updateData)) {
            $_SESSION['success_message'] = "Profile updated successfully!";
            $_SESSION['name'] = $updateData['name']; // Update session name
            header("Location: profile.php");
            exit();
        } else {
            $errors[] = "Failed to update profile information.";
        }
    }
}

// Initialize layout
$layout = new Layout('My Profile', 'profile');
$layout->renderHeader();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">My Profile</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="profile-picture-container">
                                <?php if (!empty($user['profile_pic_url'])): ?>
                                    <img src="/public/<?= htmlspecialchars($user['profile_pic_url']) ?>" 
                                         alt="Profile Picture" 
                                         class="img-fluid rounded-circle mb-3"
                                         style="max-width: 200px; height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="/public/assets/img/profile-pictures/blank-profile-picture-973460_1280.webp" 
                                         alt="Default Profile Picture" 
                                         class="img-fluid rounded-circle mb-3"
                                         style="max-width: 200px; height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <form method="post" enctype="multipart/form-data" class="mt-3">
                                <div class="mb-3">
                                    <label for="profile_pic" class="form-label">Update Profile Picture</label>
                                    <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/jpeg,image/png,image/jpg">
                                    <div class="form-text">Upload JPG, JPEG or PNG file (max 2MB)</div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-1"></i> Upload Picture
                                </button>
                            </form>
                        </div>
                        <div class="col-md-8">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" 
                                           value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                    <div class="form-text">Username cannot be changed</div>
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <input type="text" class="form-control" id="role" 
                                           value="<?= htmlspecialchars($user['role']) ?>" disabled>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$layout->renderFooter();
?>