<?php

if (empty($_POST["name"])) {
    die("Full Name is required");
}

if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Valid email is required");
}

if (empty($_POST["username"]) || strlen($_POST["username"]) < 5) {
    die("Username is required and must be at least 5 characters");
}

if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters");
}

if (!preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter");
}

if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match");
}

$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

// Include database connection
$mysqli = require __DIR__ . "/database.php";

$sql = "INSERT INTO users (full_name, username, email, password_hash, role) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $mysqli->stmt_init();

if (!$stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

// Trim inputs to prevent accidental whitespace issues
$full_name = trim($_POST["name"]);
$email = trim($_POST["email"]);
$username = trim($_POST["username"]);

// Assign role based on username or email
$admin_email_domains = ["company.com", "admin.org"]; // Add allowed admin domains
$email_domain = substr(strrchr($email, "@"), 1); // Extract domain from email

if (str_ends_with($username, '-admin') || in_array($email_domain, $admin_email_domains) || str_starts_with($email, 'admin@')) {
    $role = "Admin"; // Must match ENUM('Admin', 'Applicant') exactly
} else {
    $role = "Applicant"; // Default role for regular users
}

// Bind parameters
$stmt->bind_param("sssss", $full_name, $username, $email, $password_hash, $role);

if ($stmt->execute()) {
    echo "Signup successful. Please log in.";
    // Redirect to login page
    header("Location: login.php");
    exit();
} else {
    if ($mysqli->errno === 1062) {
        die("Email already taken");
    } else {
        die($mysqli->error . " " . $mysqli->errno);
    }
}

?>
