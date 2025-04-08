<?php
require_once __DIR__ . '/../../controllers/UserManagementController.php';
require_once '../../views/layout/layout.php';

$controller = new UserManagementController();

// Check if the ID is provided in the URL
if (!isset($_GET['id'])) {
    header("Location: listUsers.php");
    exit();
}

$user_id = $_GET['id'];
$user = $controller->getUserById($user_id);

// If user not found, redirect to the list
if (!$user) {
    header("Location: listUsers.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $controller->update($user_id, $_POST);
    header("Location: listUsers.php");
    exit();
}

// Initialize layout with page title and active menu
$layout = new Layout('Edit User', 'users');
// Render the header part
$layout->renderHeader();
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="fas fa-user-edit me-2"></i>Edit User</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Edit User: <?= htmlspecialchars($user['name']) ?></h4>
            </div>
            <div class="card-body">
                <form method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                            <div class="invalid-feedback">Please enter a name</div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            <div class="invalid-feedback">Please enter a valid email</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                            <div class="invalid-feedback">Please enter a username</div>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="Applicant" <?= $user['role'] === 'Applicant' ? 'selected' : '' ?>>Applicant</option>
                                <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="applicant_id" class="form-label">Applicant ID</label>
                            <input type="number" class="form-control" id="applicant_id" name="applicant_id" value="<?= $user['applicant_id'] ?>">
                            <div class="form-text">Required only for Applicant role</div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="listUsers.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation script
(function() {
    'use strict';
    
    // Fetch all forms we want to apply validation styles to
    var forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php
// Render the footer part
$layout->renderFooter();
?>