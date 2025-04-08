<?php

$host = "localhost";
$dbname = "transpodb";
$username = "marc";
$password = "this_is_password";

// Create a new MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Return the MySQLi connection object
return $mysqli;
