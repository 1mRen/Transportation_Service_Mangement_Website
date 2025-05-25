<?php
// Database connection
$conn = require_once __DIR__ . '/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// SQL to create reservation_vehicles table
$sql = "CREATE TABLE IF NOT EXISTS reservation_vehicles (
    reservation_vehicle_id INT PRIMARY KEY AUTO_INCREMENT,
    reservation_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservation(reservation_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicle(vehicle_id) ON DELETE CASCADE,
    UNIQUE KEY unique_reservation_vehicle (reservation_id, vehicle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Execute the query
if (mysqli_query($conn, $sql)) {
    echo "Table reservation_vehicles created successfully";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?> 