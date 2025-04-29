<?php
// layout.php

class Layout {
    private $pageTitle;
    private $username;
    private $userRole;
    private $activeMenu;

    public function __construct($pageTitle = 'Campus Transportation Booking System', $activeMenu = '') {
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
        $avatarPath = "/public/assets/images/user-pic.png";
        
        // If user has a custom avatar, use it (assuming it's stored in session or can be constructed)
        if ($isLoggedIn && isset($_SESSION['avatar'])) {
            $avatarPath = $_SESSION['avatar'];
        }
        
        ?>
        <aside id="sidebar">
            <div class="sidebar-header">
                <div class="user-info">
                    <img src="<?php echo $avatarPath; ?>" alt="User Picture" class="user-avatar">
                    <h3 class="user-name"><?php echo $this->username; ?></h3>
                    <p class="user-role"><?php echo $this->userRole; ?></p>
                    <?php if ($isLoggedIn): ?>
                        <a href="/profile.php" class="profile-link">View Profile</a>
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
                        <?php endif; ?>
                        
                        <!-- Regular menu items for all users -->
                        <li class="nav-item">
                            <a href="/src/views/vehicles/vehicles.php" class="nav-link <?php echo ($this->activeMenu === 'vehicles') ? 'active' : ''; ?>">
                                <i class="fa fa-bus"></i>
                                <span>Vehicles</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/bookings.php" class="nav-link <?php echo ($this->activeMenu === 'bookings') ? 'active' : ''; ?>">
                                <i class="fa fa-calendar"></i>
                                <span>Bookings</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/contact.php" class="nav-link <?php echo ($this->activeMenu === 'contact') ? 'active' : ''; ?>">
                                <i class="fa fa-envelope"></i>
                                <span>Contact Us</span>
                            </a>
                        </li>
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
        // Check if there are any notifications
        $notificationCount = 0;
        if (isset($_SESSION['notifications'])) {
            $notificationCount = count($_SESSION['notifications']);
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
                        <a href="/notifications.php" class="notification-btn">
                            <i class="fa fa-bell"></i>
                            <?php if ($notificationCount > 0): ?>
                                <span class="notification-badge"><?php echo $notificationCount; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
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
                            <span>© <?php echo $currentYear; ?> Campus Transportation Booking System</span>
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