<?php
// Start session if not already started
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // If logged in, redirect to dashboard or main application page
    header("Location: src/views/dashboard.php");
    exit();
} else {
    // If not logged in, redirect to login page
    header("Location: src/views/auth/signin.php");
    exit();
}
?>