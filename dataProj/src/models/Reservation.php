<?php

// Insert default reservation statuses
$reservation_statuses = [
  "Pending",
  "Approved",
  "Rejected",
  "Completed"
];

foreach ($reservation_statuses as $status) {
  $mysqli->query("INSERT IGNORE INTO `reservation_status` (`status_name`) VALUES ('$status')");
}

// Create reservation table
$reservation_table = $mysqli->query("CREATE TABLE IF NOT EXISTS `reservation` (
`reservation_id` int NOT NULL AUTO_INCREMENT,
`applicant_id` int NOT NULL,
`vehicle_id` int NOT NULL,
`date_of_use` date NOT NULL,
`departure_area` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
`destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
`departure_time` time NOT NULL,
`return_time` time NOT NULL,
`purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
`status_id` int DEFAULT '1',
PRIMARY KEY (`reservation_id`),
KEY `applicant_id` (`applicant_id`),
KEY `vehicle_id` (`vehicle_id`),
KEY `status_id` (`status_id`),
KEY `idx_reservation_applicant_id` (`applicant_id`),
CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `applicant` (`applicant_id`),
CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`vehicle_id`),
CONSTRAINT `reservation_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `reservation_status` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if (!$reservation_table) {
  die("Error creating reservation table: " . $mysqli->error);
}

// Create reserve_status_history table
$reserve_status_history_table = $mysqli->query("CREATE TABLE IF NOT EXISTS `reserve_status_history` (
`history_id` int NOT NULL AUTO_INCREMENT,
`reservation_id` int NOT NULL,
`status_id` int NOT NULL,
`updated_by` int NOT NULL,
`timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`history_id`),
KEY `reservation_id` (`reservation_id`),
KEY `status_id` (`status_id`),
KEY `updated_by` (`updated_by`),
CONSTRAINT `reserve_status_history_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`reservation_id`),
CONSTRAINT `reserve_status_history_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `reservation_status` (`status_id`),
CONSTRAINT `reserve_status_history_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if (!$reserve_status_history_table) {
  die("Error creating reserve_status_history table: " . $mysqli->error);
}

// Create reservation_documents table
$reservation_documents_table = $mysqli->query("CREATE TABLE IF NOT EXISTS `reservation_documents` (
`document_id` int NOT NULL AUTO_INCREMENT,
`reservation_id` int NOT NULL,
`document_name` varchar(255) NOT NULL,
`document_type` varchar(100) NOT NULL,
`file_path` varchar(255) NOT NULL,
`file_size` int NOT NULL,
`uploaded_by` int NOT NULL,
`upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
`notes` text,
PRIMARY KEY (`document_id`),
KEY `uploaded_by` (`uploaded_by`),
KEY `idx_reservation_documents` (`reservation_id`),
CONSTRAINT `reservation_documents_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`reservation_id`) ON DELETE CASCADE,
CONSTRAINT `reservation_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci");


?>