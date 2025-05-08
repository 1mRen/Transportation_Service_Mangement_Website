<?php

$host = "localhost";
$dbname = "transpodb";
$username = "marc";
$password = "this_is_password";

// Step 1: Connect to MySQL server WITHOUT selecting a database
$mysqli = new mysqli($host, $username, $password);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Step 2: Create the database if it doesn't exist
$db_created = $mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbname`");

if (!$db_created) {
    die('Database creation failed: ' . $mysqli->error);
}

// Step 3: Select the database
if (!$mysqli->select_db($dbname)) {
    die("Failed to select database: " . $mysqli->error);
}

?>
