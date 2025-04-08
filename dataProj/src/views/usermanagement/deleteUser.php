<?php
require_once __DIR__ . '/../../controllers/UserManagementController.php';
require_once '../../views/layout/layout.php';

$controller = new UserManagementController();

// Check if the delete confirmation was submitted
if (isset($_POST['confirm_delete']) && isset($_POST['id'])) {
    $controller->delete($_POST['id']);
    header("Location: listUsers.php");
    exit();
}

// Check if the ID is provided in the URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $user = $controller->getUserById($user_id);
    
    // Check if user exists
    if (!$user) {
        echo "User not found.";
        exit();
    }
} else {
    // No ID provided, redirect to list
    header("Location: listUsers.php");
    exit();
}

// Initialize layout with page title and active menu
$layout = new Layout('Delete User', 'users');
// Render the header part
$layout->renderHeader();
?>

<!-- Main Content -->
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-danger text-white">
                <h3 class="mb-0">Delete User</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <h4><i class="fas fa-exclamation-triangle me-2"></i>Are you sure you want to delete this user?</h4>
                    <p class="mb-0">This action cannot be undone.</p>
                </div>
                
                <div class="user-details mb-4">
                    <h5 class="border-bottom pb-2 mb-3"><i class="fas fa-user me-2"></i>User Details</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th class="bg-light" style="width: 30%">ID:</th>
                                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Name:</th>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Username:</th>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Email:</th>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Role:</th>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                </tr>
                                <?php if (isset($user['applicant_id']) && $user['applicant_id']): ?>
                                <tr>
                                    <th class="bg-light">Applicant ID:</th>
                                    <td><?php echo htmlspecialchars($user['applicant_id']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th class="bg-light">Created:</th>
                                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                </tr>
                                <?php if (isset($user['updated_at']) && $user['updated_at']): ?>
                                <tr>
                                    <th class="bg-light">Updated:</th>
                                    <td><?php echo htmlspecialchars($user['updated_at']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="action-buttons text-center">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $user_id; ?>">
                        <button type="submit" name="confirm_delete" class="btn btn-danger me-2">
                            <i class="fas fa-trash me-1"></i> Confirm Delete
                        </button>
                        <a href="listUsers.php" class="btn btn-secondary">
                            <i class="fas fa-times-circle me-1"></i> Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Render the footer part
$layout->renderFooter();
?>