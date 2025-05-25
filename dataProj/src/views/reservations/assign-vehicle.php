<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: /src/views/auth/signin.php");
    exit();
}

// Database connection
$conn = require_once __DIR__ . '/../../config/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// Check if required parameters are present
if (!isset($_POST['reservation_id']) || !isset($_POST['vehicle_ids']) || !isset($_POST['requested_vehicles'])) {
    $_SESSION['error'] = "Missing required parameters.";
    header("Location: /src/views/reservations/index.php");
    exit();
}

$reservationId = $_POST['reservation_id'];
$vehicleIds = $_POST['vehicle_ids'];
$requestedVehicles = (int)$_POST['requested_vehicles'];

// Validate number of vehicles
if (count($vehicleIds) !== $requestedVehicles) {
    $_SESSION['error'] = "Please select exactly {$requestedVehicles} vehicle(s) as requested.";
    header("Location: /src/views/reservations/view.php?id=" . $reservationId);
    exit();
}

// Debug: Log POST data
error_log('POST data: ' . print_r($_POST, true));

$vehicleIds = array_filter($vehicleIds); // Remove empty values
// Debug: Log vehicle IDs being processed
error_log('Assigning vehicles: ' . print_r($vehicleIds, true));

if (count($vehicleIds) > 0) {
    // Start transaction
    mysqli_begin_transaction($conn);
    try {
        // Verify the reservation exists and is pending
        $checkQuery = "SELECT r.*, a.user_id 
                      FROM reservation r 
                      JOIN applicant a ON r.applicant_id = a.applicant_id 
                      WHERE r.reservation_id = ? AND r.status_id = 1";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "i", $reservationId);
        mysqli_stmt_execute($checkStmt);
        $reservation = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));

        if (!$reservation) {
            throw new Exception("Reservation not found or not pending.");
        }

        // Verify all vehicles are available and have 'In Use' status
        $placeholders = str_repeat('?,', count($vehicleIds) - 1) . '?';
        $vehicleQuery = "SELECT v.*, vs.status_name 
                        FROM vehicle v 
                        JOIN vehicle_status vs ON v.status_id = vs.status_id 
                        WHERE v.vehicle_id IN ($placeholders) 
                        AND vs.status_name = 'In Use'";
        $vehicleStmt = mysqli_prepare($conn, $vehicleQuery);
        $types = str_repeat('i', count($vehicleIds));
        mysqli_stmt_bind_param($vehicleStmt, $types, ...$vehicleIds);
        mysqli_stmt_execute($vehicleStmt);
        $vehicles = mysqli_stmt_get_result($vehicleStmt);
        
        if (mysqli_num_rows($vehicles) !== count($vehicleIds)) {
            throw new Exception("One or more selected vehicles are not available or not in use.");
        }

        // Check for scheduling conflicts for all vehicles
        foreach ($vehicleIds as $vehicleId) {
            $conflictQuery = "SELECT COUNT(*) as count 
                             FROM reservation 
                             WHERE vehicle_id = ? 
                             AND reservation_id != ? 
                             AND date_of_use = ? 
                             AND status_id IN (1, 2) 
                             AND (
                                 (departure_time <= ? AND return_time >= ?) OR
                                 (departure_time <= ? AND return_time >= ?) OR
                                 (departure_time >= ? AND return_time <= ?)
                             )";
            $conflictStmt = mysqli_prepare($conn, $conflictQuery);
            mysqli_stmt_bind_param($conflictStmt, "iisssssss", 
                $vehicleId, 
                $reservationId,
                $reservation['date_of_use'],
                $reservation['departure_time'], 
                $reservation['departure_time'],
                $reservation['return_time'], 
                $reservation['return_time'],
                $reservation['departure_time'], 
                $reservation['return_time']
            );
            mysqli_stmt_execute($conflictStmt);
            $conflict = mysqli_fetch_assoc(mysqli_stmt_get_result($conflictStmt));

            if ($conflict['count'] > 0) {
                throw new Exception("One or more selected vehicles are already assigned to another reservation for this time period.");
            }
        }

        // Create a new table for multiple vehicle assignments if it doesn't exist
        $createTableQuery = "CREATE TABLE IF NOT EXISTS reservation_vehicles (
            reservation_id INT,
            vehicle_id INT,
            assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (reservation_id, vehicle_id),
            FOREIGN KEY (reservation_id) REFERENCES reservation(reservation_id),
            FOREIGN KEY (vehicle_id) REFERENCES vehicle(vehicle_id)
        )";
        mysqli_query($conn, $createTableQuery);

        // Delete any existing assignments for this reservation
        $deleteQuery = "DELETE FROM reservation_vehicles WHERE reservation_id = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($deleteStmt, "i", $reservationId);
        mysqli_stmt_execute($deleteStmt);

        // Insert new vehicle assignments
        $assignQuery = "INSERT INTO reservation_vehicles (reservation_id, vehicle_id) VALUES (?, ?)";
        $assignStmt = mysqli_prepare($conn, $assignQuery);
        
        $remarks = "Vehicles assigned:\n";
        foreach ($vehicleIds as $vehicleId) {
            // Get vehicle details for remarks
            $vehicleQuery = "SELECT type_of_vehicle, plate_no FROM vehicle WHERE vehicle_id = ?";
            $vehicleStmt = mysqli_prepare($conn, $vehicleQuery);
            mysqli_stmt_bind_param($vehicleStmt, "i", $vehicleId);
            mysqli_stmt_execute($vehicleStmt);
            $vehicle = mysqli_fetch_assoc(mysqli_stmt_get_result($vehicleStmt));
            
            // Insert assignment
            mysqli_stmt_bind_param($assignStmt, "ii", $reservationId, $vehicleId);
            if (!mysqli_stmt_execute($assignStmt)) {
                error_log('MySQL error: ' . mysqli_error($conn));
                throw new Exception("Failed to assign vehicle.");
            }
            
            $remarks .= "- " . $vehicle['type_of_vehicle'] . " - " . $vehicle['plate_no'] . "\n";
        }

        // Debug: Log after successful insert
        error_log('Inserted vehicles for reservation: ' . $reservationId);

        // Update the main reservation with the first vehicle and remarks
        $updateQuery = "UPDATE reservation SET vehicle_id = ?, remarks = CONCAT(IFNULL(remarks, ''), '\n', ?) WHERE reservation_id = ?";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "isi", $vehicleIds[0], $remarks, $reservationId);
        
        if (!mysqli_stmt_execute($updateStmt)) {
            throw new Exception("Failed to update reservation.");
        }

        // Notify the user about the vehicle assignments
        require_once __DIR__ . '/../../controllers/NotificationController.php';
        $notificationController = new \Controllers\NotificationController();
        $title = 'Vehicles Assigned to Reservation';
        $message = 'The following vehicles have been assigned to your reservation (ID: ' . $reservationId . "):\n" . $remarks;
        $notificationController->create($reservation['user_id'], $title, $message, 'info');

        mysqli_commit($conn);
        $_SESSION['success'] = "Vehicles assigned successfully.";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }
}

// Redirect back to the reservation view
header("Location: /src/views/reservations/view.php?id=" . $reservationId);
exit();
?> 