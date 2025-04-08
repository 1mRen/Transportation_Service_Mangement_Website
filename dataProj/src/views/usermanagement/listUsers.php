<?php
require_once __DIR__ . '/../../controllers/UserManagementController.php';
require_once '../../views/layout/layout.php';

$controller = new UserManagementController();
$users = $controller->index();

// Initialize layout with page title and active menu
$layout = new Layout('User Management', 'users');
// Render the header part
$layout->renderHeader();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="fas fa-users me-2"></i>User List</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="createUser.php" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i> Add New User
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center">No users found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td>
                                <span class="badge <?= $user['role'] === 'Admin' ? 'bg-danger' : 'bg-primary' ?>">
                                    <?= htmlspecialchars($user['role']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="userDetails.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-info me-1" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="editUser.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="deleteUser.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Render the footer part
$layout->renderFooter();
?>