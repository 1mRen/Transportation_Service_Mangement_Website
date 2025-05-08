<?php

// Create vehicle_status table
$vehicle_status_table = $mysqli->query("CREATE TABLE IF NOT EXISTS `vehicle_status` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if (!$vehicle_status_table) {
    die("Error creating vehicle_status table: " . $mysqli->error);
}

// Insert default vehicle statuses
$vehicle_statuses = [
    "Available",
    "In Use",
    "Under Maintenance",
    "Decommissioned"
];

foreach ($vehicle_statuses as $status) {
    $mysqli->query("INSERT IGNORE INTO `vehicle_status` (`status_name`) VALUES ('$status')");
}

// Create vehicle table
$vehicle_table = $mysqli->query("CREATE TABLE IF NOT EXISTS `vehicle` (
  `vehicle_id` int NOT NULL AUTO_INCREMENT,
  `plate_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_of_vehicle` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL,
  `status_id` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`vehicle_id`),
  UNIQUE KEY `plate_no` (`plate_no`),
  KEY `fk_vehicle_status` (`status_id`),
  CONSTRAINT `fk_vehicle_status` FOREIGN KEY (`status_id`) REFERENCES `vehicle_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if (!$vehicle_table) {
    die("Error creating vehicle table: " . $mysqli->error);
}

// Create assignment_status table
$assignment_status_table = $mysqli->query("CREATE TABLE IF NOT EXISTS `assignment_status` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if (!$assignment_status_table) {
    die("Error creating assignment_status table: " . $mysqli->error);
}

// Insert default assignment statuses
$assignment_statuses = [
    "Active",
    "Pending",
    "Cancelled",
    "Completed"
];

foreach ($assignment_statuses as $status) {
    $mysqli->query("INSERT IGNORE INTO `assignment_status` (`status_name`) VALUES ('$status')");
}

// Create vehicle_assignment table
$vehicle_assignment_table = $mysqli->query("CREATE TABLE IF NOT EXISTS `vehicle_assignment` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `vehicle_id` int NOT NULL,
  `driver_id` int NOT NULL,
  `assigned_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status_id` int DEFAULT '1',
  PRIMARY KEY (`assignment_id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `driver_id` (`driver_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `vehicle_assignment_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`vehicle_id`),
  CONSTRAINT `vehicle_assignment_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`),
  CONSTRAINT `vehicle_assignment_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `assignment_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if (!$vehicle_assignment_table) {
    die("Error creating vehicle_assignment table: " . $mysqli->error);
}

?>