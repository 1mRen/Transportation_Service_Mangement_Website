<?php

$driver_status_table = $mysqli->query("CREATE TABLE IF NOT EXISTS `driver_status` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if (!$driver_status_table) {
    die("Error creating driver_status table: " . $mysqli->error);
}

// Insert default driver statuses
$driver_statuses = [
    "Active",
    "On Leave",
    "Retired"
];

foreach ($driver_statuses as $status) {
    $mysqli->query("INSERT IGNORE INTO `driver_status` (`status_name`) VALUES ('$status')");
}

// Create driver table
$driver_table = $mysqli->query("CREATE TABLE IF NOT EXISTS `driver` (
  `driver_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` int NOT NULL,
  `driver_license_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_no` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int NOT NULL,
  `profile_pic_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL path to driver profile picture',
  PRIMARY KEY (`driver_id`),
  UNIQUE KEY `driver_license_no` (`driver_license_no`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `driver_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `driver_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if (!$driver_table) {
    die("Error creating driver table: " . $mysqli->error);
}

?>