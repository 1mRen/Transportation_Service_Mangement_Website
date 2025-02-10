<?php

if (empty($_POST["name"])) { // Ensure the correct field name
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

$sql = "INSERT INTO users (full_name, username, email, password_hash) 
        VALUES (?, ?, ?, ?)";

$stmt = $mysqli->stmt_init();

if (!$stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

// Trim inputs to prevent accidental whitespace issues
$full_name = trim($_POST["name"]);
$email = trim($_POST["email"]);
$username = trim($_POST["username"]);

$stmt->bind_param("ssss", $full_name, $username, $email, $password_hash);

if ($stmt->execute()) {
    echo "Signup Successful";
} else {
    
    if ($mysqli->errno === 1062) {
        die("email already taken");
    } else {
        die($mysqli->error . " " . $myqli->errno);
    }
}
?>