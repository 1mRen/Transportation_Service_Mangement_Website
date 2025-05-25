<?php
// Database connection
$conn = require_once __DIR__ . '/database.php';
if (!$conn) {
    die("Database connection failed.");
}

// First check if column exists
$checkColumn = "SHOW COLUMNS FROM reservation LIKE 'type_of_vehicle'";
$result = mysqli_query($conn, $checkColumn);

if (mysqli_num_rows($result) == 0) {
    // SQL to add type_of_vehicle column
    $sql = "ALTER TABLE reservation 
            ADD COLUMN type_of_vehicle VARCHAR(100) NOT NULL DEFAULT 'Bus' AFTER vehicle_id,
            ADD CONSTRAINT check_vehicle_type 
            CHECK (type_of_vehicle IN ('Bus', 'Coaster', 'Canter', 'Private Vehicle'))";

    // Execute the query
    if (mysqli_query($conn, $sql)) {
        echo "Column type_of_vehicle added successfully to reservation table";
        
        // Update existing records to have a default value
        $updateSql = "UPDATE reservation SET type_of_vehicle = 'Bus' WHERE type_of_vehicle IS NULL";
        if (mysqli_query($conn, $updateSql)) {
            echo "<br>Updated existing records with default vehicle type";
        }
    } else {
        echo "Error adding column: " . mysqli_error($conn);
    }
} else {
    echo "Column type_of_vehicle already exists in reservation table";
}

// Close connection
mysqli_close($conn);
?> 