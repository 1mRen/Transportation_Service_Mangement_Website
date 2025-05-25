<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: /src/views/auth/signin.php");
    exit();
}

// Include required files
require_once __DIR__ . '/../controllers/NotificationController.php';

// Initialize NotificationController
$notificationController = new \Controllers\NotificationController();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
        $notificationController->markAsRead($_POST['notification_id']);
    } elseif (isset($_POST['mark_all_read'])) {
        $notificationController->markAllAsRead($_SESSION['id']);
    } elseif (isset($_POST['delete']) && isset($_POST['notification_id'])) {
        $notificationController->delete($_POST['notification_id']);
    }
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get all notifications for the user
$notifications = $notificationController->getNotifications($_SESSION['id']);

// Include layout
require_once __DIR__ . '/layout/layout.php';

// Create layout instance
$layout = new Layout('Notifications', 'notifications');

// Render header
$layout->renderHeader();
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Notifications</h1>
        <?php if (!empty($notifications)): ?>
        <form method="POST" class="d-inline">
            <button type="submit" name="mark_all_read" class="btn btn-primary">
                <i class="fa fa-check-double"></i> Mark All as Read
            </button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Notifications List -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5">
                    <i class="fa fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5>No notifications</h5>
                    <p class="text-muted">You don't have any notifications at the moment.</p>
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item list-group-item-action <?= $notification['is_read'] ? '' : 'bg-light' ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-<?= $notification['type'] === 'success' ? 'check-circle' : ($notification['type'] === 'warning' ? 'exclamation-triangle' : ($notification['type'] === 'danger' ? 'times-circle' : 'info-circle')) ?> text-<?= $notification['type'] ?>"></i>
                                        <?= htmlspecialchars($notification['title']) ?>
                                    </h6>
                                    <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                    <small class="text-muted"><?= date('M d, Y h:i A', strtotime($notification['created_at'])) ?></small>
                                </div>
                                <div class="btn-group">
                                    <?php if (!$notification['is_read']): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="notification_id" value="<?= $notification['notification_id'] ?>">
                                            <button type="submit" name="mark_read" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-check"></i> Mark as Read
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="notification_id" value="<?= $notification['notification_id'] ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this notification?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Render footer
$layout->renderFooter();
?> 