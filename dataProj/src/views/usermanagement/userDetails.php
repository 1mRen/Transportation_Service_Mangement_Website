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

// Initialize layout with page title and active menu
$layout = new Layout('User Details', 'users');
// Render the header part
$layout->renderHeader();
?>

<div class="row mb-4">
    <div class="col">
        <h2><i class="fas fa-user me-2"></i>User Details</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <?php if ($user): ?>
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">User Information: <?= htmlspecialchars($user['name']) ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4 text-center">
                                <div class="avatar-placeholder rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                                    <i class="fas fa-user fa-5x text-white"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2 mb-3">Account Information</h5>
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th style="width:40%">User ID:</th>
                                            <td><?= htmlspecialchars($user['user_id']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Role:</th>
                                            <td>
                                                <span class="badge <?= $user['role'] === 'Admin' ? 'bg-danger' : 'bg-primary' ?>">
                                                    <?= htmlspecialchars($user['role']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created At:</th>
                                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                                        </tr>
                                        <?php if (isset($user['updated_at']) && $user['updated_at']): ?>
                                            <tr>
                                                <th>Updated At:</th>
                                                <td><?= htmlspecialchars($user['updated_at']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">Personal Information</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th style="width:25%">Full Name:</th>
                                            <td><?= htmlspecialchars($user['name']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Username:</th>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                        </tr>
                                        <?php if (isset($user['applicant_id']) && $user['applicant_id']): ?>
                                            <tr>
                                                <th>Applicant ID:</th>
                                                <td><?= htmlspecialchars($user['applicant_id']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="listUsers.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-list me-1"></i> Back to List
                        </a>
                        <a href="editUser.php?id=<?= $user['user_id'] ?>" class="btn btn-warning me-md-2">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="deleteUser.php?id=<?= $user['user_id'] ?>" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <h4><i class="fas fa-exclamation-circle me-2"></i>User not found</h4>
                <p>The requested user does not exist or has been deleted.</p>
                <a href="listUsers.php" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left me-1"></i> Back to User List
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Render the footer part
$layout->renderFooter();
?>