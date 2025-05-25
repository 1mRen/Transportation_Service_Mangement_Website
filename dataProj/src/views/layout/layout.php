<?php
// layout.php
require_once __DIR__ . '/../../controllers/UserManagementController.php';

class Layout {
    private $pageTitle;
    private $username;
    private $userRole;
    private $activeMenu;

    public function __construct($pageTitle = 'Campus Transportation Reservation System', $activeMenu = '') {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->pageTitle = $pageTitle;
        
        // Get actual user data from session
        $this->username = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';
        $this->userRole = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Visitor';
        $this->activeMenu = $activeMenu;
    }

    public function renderHeader() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $this->pageTitle; ?></title>
            <!-- Bootstrap CSS -->
            <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
            <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
            <!-- Custom CSS -->
            <link rel="stylesheet" href="/public/assets/css/dashboard-style.css">
        </head>
        <body>
            <div class="wrapper">
                <!-- Sidebar -->
                <?php $this->renderSidebar(); ?>

                <!-- Main Content -->
                <main id="content">
                    <!-- Top Navigation Bar -->
                    <?php $this->renderNavbar(); ?>

                    <!-- Main Container -->
                    <div class="container-fluid">
                        <!-- Announcement Banner -->
                        <?php $this->renderAnnouncement(); ?>
        <?php
    }

    private function renderSidebar() {
        // Check if user is logged in
        $isLoggedIn = isset($_SESSION['id']);
        
        // Default avatar path
        $avatarPath = "/public/assets/img/profile-pictures/blank-profile-picture-973460_1280.webp";
        
        // If user has a custom avatar, use it
        if ($isLoggedIn) {
            $controller = new UserManagementController();
            $user = $controller->getUserById($_SESSION['id']);
            if ($user && !empty($user['profile_pic_url'])) {
                $avatarPath = "/public/" . $user['profile_pic_url'];
            }
        }
        
        ?>
        <aside id="sidebar">
            <div class="sidebar-header">
                <div class="user-info">
                    <img src="<?php echo $avatarPath; ?>" alt="User Picture" class="user-avatar">
                    <h3 class="user-name"><?php echo $this->username; ?></h3>
                    <p class="user-role"><?php echo $this->userRole; ?></p>
                    <?php if ($isLoggedIn): ?>
                        <a href="/src/views/user/profile.php" class="profile-link">View Profile</a>
                    <?php else: ?>
                        <a href="/src/views/auth/signin.php" class="profile-link">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <?php if ($isLoggedIn): ?>
                        <!-- Show admin-specific menu items -->
                        <?php if (strtolower($this->userRole) === 'admin'): ?>
                            <li class="nav-item">
                                <a href="/src/views/admin/dashboard.php" class="nav-link <?php echo ($this->activeMenu === 'dashboard') ? 'active' : ''; ?>">
                                    <i class="fa fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/src/views/usermanagement/listUsers.php" class="nav-link <?php echo ($this->activeMenu === 'users') ? 'active' : ''; ?>">
                                    <i class="fa fa-users"></i>
                                    <span>User Management</span>
                                </a>
                            </li>
                            <!-- Add Drivers Management -->
                            <li class="nav-item">
                                <a href="/src/views/drivers/index.php" class="nav-link <?php echo ($this->activeMenu === 'drivers') ? 'active' : ''; ?>">
                                    <i class="fa fa-id-card"></i>
                                    <span>Drivers</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/src/views/vehicles/vehicles.php" class="nav-link <?php echo ($this->activeMenu === 'vehicles') ? 'active' : ''; ?>">
                                    <i class="fa fa-bus"></i>
                                    <span>Vehicles</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/src/views/reservations/index.php" class="nav-link <?php echo ($this->activeMenu === 'reservations') ? 'active' : ''; ?>">
                                    <i class="fa fa-calendar-check"></i>
                                    <span>Reservations</span>
                                </a>
                            </li>
                            
                        <?php else: ?>
                            <!-- Regular menu items for all users -->
                            <li class="nav-item">
                                <a href="/src/views/user/dashboard.php" class="nav-link <?php echo ($this->activeMenu === 'dashboard') ? 'active' : ''; ?>">
                                    <i class="fa fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/src/views/reservations/my-reservations.php" class="nav-link <?php echo ($this->activeMenu === 'reservations') ? 'active' : ''; ?>">
                                    <i class="fa fa-calendar-check"></i>
                                    <span>My Reservations</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/src/views/user/profile.php" class="nav-link <?php echo ($this->activeMenu === 'profile') ? 'active' : ''; ?>">
                                    <i class="fa fa-user"></i>
                                    <span>Profile</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/src/views/vehicles/vehicles.php" class="nav-link <?php echo ($this->activeMenu === 'vehicles') ? 'active' : ''; ?>">
                                    <i class="fa fa-bus"></i>
                                    <span>Vehicles</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Menu items for guests -->
                        <li class="nav-item">
                            <a href="/src/views/auth/signin.php" class="nav-link">
                                <i class="fa fa-sign-in-alt"></i>
                                <span>Sign In</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/src/views/auth/signup.php" class="nav-link">
                                <i class="fa fa-user-plus"></i>
                                <span>Sign Up</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <?php if ($isLoggedIn): ?>
                    <a href="/src/logout.php" class="logout-btn">
                        <i class="fa fa-sign-out-alt"></i> Log out
                    </a>
                <?php else: ?>
                    <a href="/index.php" class="logout-btn">
                        <i class="fa fa-home"></i> Home
                    </a>
                <?php endif; ?>
            </div>
        </aside>
        <?php
    }

    private function renderNavbar() {
        // Get unread notification count and recent notifications
        $notificationCount = 0;
        $recentNotifications = [];
        if (isset($_SESSION['id'])) {
            require_once __DIR__ . '/../../controllers/NotificationController.php';
            $notificationController = new \Controllers\NotificationController();
            $notificationCount = $notificationController->getUnreadCount($_SESSION['id']);
            $recentNotifications = $notificationController->getNotifications($_SESSION['id'], 5); // Get 5 most recent notifications
        }
        
        ?>
        <nav class="navbar">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" id="sidebarCollapse" class="btn btn-toggle">
                        <i class="fa fa-bars"></i>
                    </button>
                    <h2 class="navbar-title"><?php echo $this->pageTitle; ?></h2>
                </div>
                <div class="navbar-right">
                    <?php if (isset($_SESSION['id'])): ?>
                        <div class="dropdown">
                            <a href="#" class="notification-btn dropdown-toggle" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell"></i>
                            <?php if ($notificationCount > 0): ?>
                                <span class="notification-badge"><?php echo $notificationCount; ?></span>
                            <?php endif; ?>
                        </a>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                                <div class="dropdown-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Notifications</h6>
                                    <a href="/src/views/notifications.php" class="text-decoration-none">View All</a>
                                </div>
                                <div class="notification-list">
                                    <?php if (empty($recentNotifications)): ?>
                                        <div class="dropdown-item text-center py-3">
                                            <p class="text-muted mb-0">No notifications</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recentNotifications as $notification): ?>
                                            <a class="dropdown-item notification-item <?= $notification['is_read'] ? '' : 'unread' ?>" href="/src/views/notifications.php">
                                                <div class="d-flex align-items-center">
                                                    <div class="notification-icon me-3">
                                                        <i class="fas fa-<?= $notification['type'] === 'success' ? 'check-circle' : ($notification['type'] === 'warning' ? 'exclamation-triangle' : ($notification['type'] === 'danger' ? 'times-circle' : 'info-circle')) ?> text-<?= $notification['type'] ?>"></i>
                                                    </div>
                                                    <div class="notification-content">
                                                        <h6 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                                                        <p class="mb-1 small"><?= htmlspecialchars($notification['message']) ?></p>
                                                        <small class="text-muted"><?= date('M d, Y h:i A', strtotime($notification['created_at'])) ?></small>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <style>
            .notification-dropdown {
                width: 350px;
                max-height: 400px;
                overflow-y: auto;
                padding: 0;
            }
            
            .notification-dropdown .dropdown-header {
                padding: 10px 15px;
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
            }
            
            .notification-item {
                padding: 10px 15px;
                border-bottom: 1px solid #dee2e6;
                white-space: normal;
            }
            
            .notification-item:hover {
                background-color: #f8f9fa;
            }
            
            .notification-item.unread {
                background-color: #f0f7ff;
            }
            
            .notification-icon {
                font-size: 1.2rem;
            }
            
            .notification-content {
                flex: 1;
            }
            
            .notification-content h6 {
                font-size: 0.9rem;
                margin-bottom: 0.25rem;
            }
            
            .notification-content p {
                font-size: 0.8rem;
                color: #6c757d;
            }
            
            .notification-badge {
                position: absolute;
                top: -5px;
                right: -5px;
                background-color: #dc3545;
                color: white;
                border-radius: 50%;
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .notification-btn {
                position: relative;
                color: #6c757d;
                text-decoration: none;
                padding: 0.5rem;
            }
            
            .notification-btn:hover {
                color: #495057;
            }
        </style>
        <?php
    }

    private function renderAnnouncement() {
        // Get announcement from database or configuration
        // Here you could connect to your database to get the latest announcement
        // For now, check if there's an announcement in session
        $announcement = isset($_SESSION['announcement']) ? $_SESSION['announcement'] : null;
        
        // Only show the announcement section if there is one
        if ($announcement): 
        ?>
        <div class="announcement-banner">
            <i class="fa fa-bullhorn"></i>
            <strong>Announcement:</strong>
            <span><?php echo htmlspecialchars($announcement); ?></span>
        </div>
        <?php 
        endif;
    }

    public function renderFooter() {
        $currentYear = date('Y');
        ?>
                    </div>
                    
                    <!-- Footer -->
                    <footer class="footer">
                        <div class="container-fluid">
                            <span>© <?php echo $currentYear; ?> Campus Transportation Reservation System</span>
                        </div>
                    </footer>
                </main>
            </div>

            <!-- Bootstrap and jQuery JS -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
            
            <!-- Custom Script -->
            <script>
                $(document).ready(function() {
                    // Toggle sidebar
                    $('#sidebarCollapse').on('click', function() {
                        $('#sidebar').toggleClass('active');
                        $('#content').toggleClass('active');
                    });
                });
            </script>
        </body>
        </html>
        <?php
    }
}
?>