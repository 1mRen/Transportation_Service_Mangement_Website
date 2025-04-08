<?php
session_start();
if(!isset($_SESSION['id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: /");
    exit();
}

$username = $_SESSION['username'];
$full_name = $_SESSION['name'];
$role = $_SESSION['role']; // Get the role from session
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home Page</title>
</head>
<body>
    <h1>Home Page</h1>
    <p>Welcome, <?php echo htmlspecialchars($full_name); ?>!</p>
    <p>Your role: <strong><?php echo htmlspecialchars($role); ?></strong></p>
    
    <?php if ($role === "Admin"): ?>
        <p><a href="/admin_dashboard.php">Go to Admin Dashboard</a></p>
    <?php endif; ?>

    <a href="/src/logout.php">Logout</a>
</body>
</html>