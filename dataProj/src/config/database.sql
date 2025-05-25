-- Create reservation_vehicles table for multiple vehicle assignments
CREATE TABLE IF NOT EXISTS reservation_vehicles (
    reservation_vehicle_id INT PRIMARY KEY AUTO_INCREMENT,
    reservation_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservation(reservation_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicle(vehicle_id) ON DELETE CASCADE,
    UNIQUE KEY unique_reservation_vehicle (reservation_id, vehicle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 