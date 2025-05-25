<?php
// End Assignment Script using VehicleController's endAssignment method
// This script ends a vehicle assignment immediately when visited.

session_start();

// Only allow admins or authorized users (optional: add your own check here)
if (!isset($_SESSION['id']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: /src/views/auth/signin.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/VehicleController.php';

$conn = require __DIR__ . '/../../config/database.php';
$vehicleController = new \Controllers\VehicleController($conn);

$assignmentId = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) :
                (isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0);
if ($assignmentId <= 0) {
    $_SESSION['error_message'] = 'Invalid assignment ID.';
    header('Location: /src/views/vehicles/vehicles.php');
    exit();
}

$today = date('Y-m-d');
$result = $vehicleController->endAssignment($assignmentId, $today);

if ($result['success']) {
    $_SESSION['success_message'] = $result['message'];
} else {
    $_SESSION['error_message'] = $result['message'];
}

header('Location: /src/views/vehicles/vehicles.php');
exit(); 