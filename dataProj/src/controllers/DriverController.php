<?php
/**
 * Driver Controller
 * 
 * This controller handles all driver-related operations including:
 * - Listing all drivers
 * - Adding new drivers
 * - Updating driver information
 * - Viewing driver details
 * - Deleting drivers
 * - Managing driver status
 * - Viewing driver assignments
 */

class DriverController {
    private $db;
    
    /**
     * Constructor - initializes database connection
     */
    public function __construct() {
        // The issue is here - database.php returns the connection, but we need to store it properly
        // Change from require_once to directly setting $this->db
        $this->db = require __DIR__ . '/../config/database.php';
        
        // Add a safety check to ensure $this->db is a mysqli object
        if (!($this->db instanceof mysqli)) {
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Get all drivers with their status information
     * 
     * @return array Array of driver records
     */
    public function getAllDrivers() {
        $query = "SELECT d.*, ds.status_name 
                 FROM driver d
                 JOIN driver_status ds ON d.status_id = ds.status_id
                 ORDER BY d.driver_id DESC";
                 
        $result = $this->db->query($query);
        $drivers = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $drivers[] = $row;
            }
        }
        
        return $drivers;
    }
    
    /**
     * Get a specific driver by ID
     * 
     * @param int $driverId The driver ID
     * @return array|false Driver data or false if not found
     */
    public function getDriverById($driverId) {
        $stmt = $this->db->prepare("SELECT d.*, ds.status_name 
                                   FROM driver d
                                   JOIN driver_status ds ON d.status_id = ds.status_id
                                   WHERE d.driver_id = ?");
        $stmt->bind_param("i", $driverId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Get all driver statuses
     * 
     * @return array Array of status records
     */
    public function getAllDriverStatuses() {
        $query = "SELECT * FROM driver_status ORDER BY status_name";
        $result = $this->db->query($query);
        $statuses = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $statuses[] = $row;
            }
        }
        
        return $statuses;
    }
    
    /**
     * Create a new driver
     * 
     * @param array $driverData Driver information
     * @return bool|int Driver ID on success, false on failure
     */
    public function createDriver($driverData) {
        try {
            $stmt = $this->db->prepare("INSERT INTO driver (full_name, age, driver_license_no, contact_no, status_id, profile_pic_url) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param(
                "sissss", 
                $driverData['full_name'], 
                $driverData['age'], 
                $driverData['driver_license_no'], 
                $driverData['contact_no'], 
                $driverData['status_id'], 
                $driverData['profile_pic_url']
            );
            
            if ($stmt->execute()) {
                return $this->db->insert_id;
            }
            
            return false;
        } catch (Exception $e) {
            // Log error
            error_log("Error creating driver: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update driver information
     * 
     * @param int $driverId Driver ID
     * @param array $driverData Driver information
     * @return bool Success or failure
     */
    public function updateDriver($driverId, $driverData) {
        try {
            // If profile pic URL is not provided, don't update it
            if (isset($driverData['profile_pic_url']) && $driverData['profile_pic_url']) {
                $stmt = $this->db->prepare("UPDATE driver 
                                           SET full_name = ?, 
                                               age = ?, 
                                               driver_license_no = ?, 
                                               contact_no = ?, 
                                               status_id = ?, 
                                               profile_pic_url = ? 
                                           WHERE driver_id = ?");
                
                $stmt->bind_param(
                    "sissssi", 
                    $driverData['full_name'], 
                    $driverData['age'], 
                    $driverData['driver_license_no'], 
                    $driverData['contact_no'], 
                    $driverData['status_id'],
                    $driverData['profile_pic_url'],
                    $driverId
                );
            } else {
                $stmt = $this->db->prepare("UPDATE driver 
                                           SET full_name = ?, 
                                               age = ?, 
                                               driver_license_no = ?, 
                                               contact_no = ?, 
                                               status_id = ? 
                                           WHERE driver_id = ?");
                
                $stmt->bind_param(
                    "sisssi", 
                    $driverData['full_name'], 
                    $driverData['age'], 
                    $driverData['driver_license_no'], 
                    $driverData['contact_no'], 
                    $driverData['status_id'],
                    $driverId
                );
            }
            
            return $stmt->execute();
        } catch (Exception $e) {
            // Log error
            error_log("Error updating driver: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a driver
     * 
     * @param int $driverId Driver ID
     * @return bool Success or failure
     */
    public function deleteDriver($driverId) {
        try {
            // Check if driver is in any assignments
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM vehicle_assignment WHERE driver_id = ?");
            $stmt->bind_param("i", $driverId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            // If driver has assignments, don't delete
            if ($row['count'] > 0) {
                return false;
            }
            
            $stmt = $this->db->prepare("DELETE FROM driver WHERE driver_id = ?");
            $stmt->bind_param("i", $driverId);
            return $stmt->execute();
        } catch (Exception $e) {
            // Log error
            error_log("Error deleting driver: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update driver status
     * 
     * @param int $driverId Driver ID
     * @param int $statusId New status ID
     * @return bool Success or failure
     */
    public function updateDriverStatus($driverId, $statusId) {
        try {
            $stmt = $this->db->prepare("UPDATE driver SET status_id = ? WHERE driver_id = ?");
            $stmt->bind_param("ii", $statusId, $driverId);
            return $stmt->execute();
        } catch (Exception $e) {
            // Log error
            error_log("Error updating driver status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get driver's vehicle assignments
     * 
     * @param int $driverId Driver ID
     * @return array Array of assignment records
     */
    public function getDriverAssignments($driverId) {
        $stmt = $this->db->prepare("SELECT va.*, v.plate_no, v.type_of_vehicle, ast.status_name as assignment_status
                                   FROM vehicle_assignment va
                                   JOIN vehicle v ON va.vehicle_id = v.vehicle_id
                                   JOIN assignment_status ast ON va.status_id = ast.status_id
                                   WHERE va.driver_id = ?
                                   ORDER BY va.assigned_date DESC");
        $stmt->bind_param("i", $driverId);
        $stmt->execute();
        $result = $stmt->get_result();
        $assignments = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $assignments[] = $row;
            }
        }
        
        return $assignments;
    }
    
    /**
     * Get active drivers (available for assignments)
     * 
     * @return array Array of active driver records
     */
    public function getActiveDrivers() {
        // Get active status ID (assuming 1 is Active)
        $activeStatusId = 1;
        
        $stmt = $this->db->prepare("SELECT * FROM driver WHERE status_id = ? ORDER BY full_name");
        $stmt->bind_param("i", $activeStatusId);
        $stmt->execute();
        $result = $stmt->get_result();
        $drivers = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $drivers[] = $row;
            }
        }
        
        return $drivers;
    }
    
    /**
     * Check if driver license number already exists (for validation)
     * 
     * @param string $licenseNo Driver license number to check
     * @param int|null $excludeDriverId Driver ID to exclude from check (for updates)
     * @return bool True if exists, false otherwise
     */
    public function driverLicenseExists($licenseNo, $excludeDriverId = null) {
        if ($excludeDriverId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM driver 
                                       WHERE driver_license_no = ? AND driver_id != ?");
            $stmt->bind_param("si", $licenseNo, $excludeDriverId);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM driver 
                                       WHERE driver_license_no = ?");
            $stmt->bind_param("s", $licenseNo);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }
    
    /**
     * Upload driver profile picture
     * 
     * @param array $file File data from $_FILES
     * @return string|false Path to uploaded file or false on failure
     */
    public function uploadProfilePicture($file) {
        $targetDir = __DIR__ . "/../../public/uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        
        // Validate file type
        if (!in_array($fileExt, $allowedTypes)) {
            return false;
        }
        
        // Generate unique filename
        $fileName = uniqid('driver_') . '.' . $fileExt;
        $targetFile = $targetDir . $fileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return 'uploads/' . $fileName;
        }
        
        return false;
    }
    
    /**
     * Get drivers with assignments on a specific date
     * 
     * @param string $date Date in Y-m-d format
     * @return array Array of driver assignments
     */
    public function getDriversAssignedOnDate($date) {
        $stmt = $this->db->prepare("SELECT d.driver_id, d.full_name, v.vehicle_id, v.plate_no, v.type_of_vehicle
                                  FROM vehicle_assignment va
                                  JOIN driver d ON va.driver_id = d.driver_id
                                  JOIN vehicle v ON va.vehicle_id = v.vehicle_id
                                  WHERE ? BETWEEN va.assigned_date AND IFNULL(va.end_date, ?)
                                  AND va.status_id = 1");
        $stmt->bind_param("ss", $date, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $assignments = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $assignments[] = $row;
            }
        }
        
        return $assignments;
    }
    
    /**
     * Get available drivers on a specific date (not assigned to any vehicle)
     * 
     * @param string $date Date in Y-m-d format
     * @return array Array of available driver records
     */
    public function getAvailableDriversOnDate($date) {
        $stmt = $this->db->prepare("SELECT d.* 
                                  FROM driver d
                                  WHERE d.status_id = 1
                                  AND d.driver_id NOT IN (
                                      SELECT va.driver_id
                                      FROM vehicle_assignment va
                                      WHERE ? BETWEEN va.assigned_date AND IFNULL(va.end_date, ?)
                                      AND va.status_id = 1
                                  )
                                  ORDER BY d.full_name");
        $stmt->bind_param("ss", $date, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $drivers = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $drivers[] = $row;
            }
        }
        
        return $drivers;
    }
    
    /**
     * Get driver statistics
     * 
     * @return array Statistics data
     */
    public function getDriverStatistics() {
        $stats = [
            'total' => 0,
            'active' => 0,
            'on_leave' => 0,
            'retired' => 0,
            'assigned' => 0
        ];
        
        // Get total count
        $result = $this->db->query("SELECT COUNT(*) as count FROM driver");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['total'] = $row['count'];
        }
        
        // Get status count
        $result = $this->db->query("SELECT ds.status_name, COUNT(*) as count 
                                    FROM driver d
                                    JOIN driver_status ds ON d.status_id = ds.status_id
                                    GROUP BY d.status_id");
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $status = strtolower(str_replace(' ', '_', $row['status_name']));
                $stats[$status] = $row['count'];
            }
        }
        
        // Get currently assigned driver count
        $today = date('Y-m-d');
        $result = $this->db->query("SELECT COUNT(DISTINCT driver_id) as count 
                                   FROM vehicle_assignment 
                                   WHERE '$today' BETWEEN assigned_date AND IFNULL(end_date, '$today')
                                   AND status_id = 1");
        
        if ($result && $row = $result->fetch_assoc()) {
            $stats['assigned'] = $row['count'];
        }
        
        return $stats;
    }
    
    /**
     * Destructor - closes database connection
     * We only close the connection if it's a valid mysqli object
     */
    public function __destruct() {
        if ($this->db instanceof mysqli) {
            $this->db->close();
        }
    }
}