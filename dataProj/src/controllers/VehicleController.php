<?php

namespace Controllers;

class VehicleController
{
    private $db;

    public function __construct($mysqli)
    {
        $this->db = $mysqli;
    }

    /**
     * Get all vehicles with their status names
     * 
     * @return array Vehicles list
     */
    public function getAllVehicles()
    {
        try {
            $query = "SELECT v.*, s.status_name 
                      FROM vehicle v 
                      JOIN vehicle_status s ON v.status_id = s.status_id
                      ORDER BY v.vehicle_id";
            $result = $this->db->query($query);
            
            if (!$result) {
                error_log("Error fetching vehicles: " . $this->db->error);
                return [];
            }
            
            $vehicles = [];
            while ($row = $result->fetch_assoc()) {
                $vehicles[] = $row;
            }
            
            return $vehicles;
        } catch (\Exception $e) {
            error_log("Error fetching vehicles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a specific vehicle by ID
     * 
     * @param int $id Vehicle ID
     * @return array|false Vehicle data or false if not found
     */
    public function getVehicleById($id)
    {
        try {
            $query = "SELECT v.*, s.status_name 
                      FROM vehicle v 
                      JOIN vehicle_status s ON v.status_id = s.status_id
                      WHERE v.vehicle_id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Error fetching vehicle: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available vehicles (status = Available)
     * 
     * @return array Available vehicles
     */
    public function getAvailableVehicles()
    {
        try {
            // Status ID 1 is 'Available' based on your schema data
            $query = "SELECT v.*, s.status_name 
                      FROM vehicle v 
                      JOIN vehicle_status s ON v.status_id = s.status_id
                      WHERE v.status_id = 1
                      ORDER BY v.vehicle_id";
            
            $result = $this->db->query($query);
            
            if (!$result) {
                error_log("Error fetching available vehicles: " . $this->db->error);
                return [];
            }
            
            $vehicles = [];
            while ($row = $result->fetch_assoc()) {
                $vehicles[] = $row;
            }
            
            return $vehicles;
        } catch (\Exception $e) {
            error_log("Error fetching available vehicles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new vehicle
     * 
     * @param string $plateNo Vehicle plate number
     * @param string $type Type of vehicle
     * @param int $capacity Vehicle capacity
     * @param int $statusId Vehicle status ID (default 1 = Available)
     * @return array Success/failure with message and ID if successful
     */
    public function createVehicle($plateNo, $type, $capacity, $statusId = 1)
    {
        try {
            // Validate plate number to ensure uniqueness
            if ($this->plateNumberExists($plateNo)) {
                return ['success' => false, 'message' => 'Plate number already exists'];
            }
            
            $query = "INSERT INTO vehicle (plate_no, type_of_vehicle, capacity, status_id) 
                      VALUES (?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssii", $plateNo, $type, $capacity, $statusId);
            $result = $stmt->execute();
            
            if ($result) {
                return ['success' => true, 'vehicle_id' => $this->db->insert_id];
            } else {
                return ['success' => false, 'message' => 'Failed to create vehicle: ' . $this->db->error];
            }
        } catch (\Exception $e) {
            error_log("Error creating vehicle: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Update an existing vehicle
     * 
     * @param int $id Vehicle ID
     * @param string $plateNo Vehicle plate number
     * @param string $type Type of vehicle
     * @param int $capacity Vehicle capacity
     * @param int $statusId Vehicle status ID
     * @return array Success/failure with message
     */
    public function updateVehicle($id, $plateNo, $type, $capacity, $statusId)
    {
        try {
            // Check if plate number exists on another vehicle
            if ($this->plateNumberExistsExcept($plateNo, $id)) {
                return ['success' => false, 'message' => 'Plate number already exists on another vehicle'];
            }
            
            $query = "UPDATE vehicle 
                      SET plate_no = ?, 
                          type_of_vehicle = ?, 
                          capacity = ?, 
                          status_id = ?
                      WHERE vehicle_id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssiis", $plateNo, $type, $capacity, $statusId, $id);
            $result = $stmt->execute();
            
            return ['success' => $result, 'message' => $result ? 'Vehicle updated successfully' : 'Failed to update vehicle: ' . $this->db->error];
        } catch (\Exception $e) {
            error_log("Error updating vehicle: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Update vehicle status
     * 
     * @param int $id Vehicle ID
     * @param int $statusId New status ID
     * @return bool Success/failure
     */
    public function updateVehicleStatus($id, $statusId)
    {
        try {
            $query = "UPDATE vehicle SET status_id = ? WHERE vehicle_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $statusId, $id);
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error updating vehicle status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a vehicle
     * 
     * @param int $id Vehicle ID
     * @return array Success/failure with message
     */
    public function deleteVehicle($id)
    {
        try {
            // Check if vehicle is currently assigned or has reservations
            if ($this->vehicleHasAssignments($id) || $this->vehicleHasReservations($id)) {
                return ['success' => false, 'message' => 'Cannot delete vehicle with active assignments or reservations'];
            }
            
            $query = "DELETE FROM vehicle WHERE vehicle_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            
            $result = $stmt->execute();
            return ['success' => $result, 'message' => $result ? 'Vehicle deleted successfully' : 'Failed to delete vehicle: ' . $this->db->error];
        } catch (\Exception $e) {
            error_log("Error deleting vehicle: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get all vehicle statuses
     * 
     * @return array Status list
     */
    public function getAllVehicleStatuses()
    {
        try {
            $query = "SELECT * FROM vehicle_status ORDER BY status_id";
            $result = $this->db->query($query);
            
            if (!$result) {
                error_log("Error fetching vehicle statuses: " . $this->db->error);
                return [];
            }
            
            $statuses = [];
            while ($row = $result->fetch_assoc()) {
                $statuses[] = $row;
            }
            
            return $statuses;
        } catch (\Exception $e) {
            error_log("Error fetching vehicle statuses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get current assignments for a vehicle
     * 
     * @param int $vehicleId Vehicle ID
     * @return array Assignments
     */
    public function getVehicleAssignments($vehicleId)
    {
        try {
            $query = "SELECT va.*, d.full_name as driver_name, s.status_name  
                      FROM vehicle_assignment va
                      JOIN driver d ON va.driver_id = d.driver_id
                      JOIN assignment_status s ON va.status_id = s.status_id
                      WHERE va.vehicle_id = ?
                      ORDER BY va.assigned_date DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $vehicleId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $assignments = [];
            while ($row = $result->fetch_assoc()) {
                $assignments[] = $row;
            }
            
            return $assignments;
        } catch (\Exception $e) {
            error_log("Error fetching vehicle assignments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new vehicle assignment
     * 
     * @param int $vehicleId Vehicle ID
     * @param int $driverId Driver ID
     * @param string $assignedDate Assignment start date
     * @param string|null $endDate Assignment end date
     * @return array Success/failure with message and ID if successful
     */
    public function assignVehicleToDriver($vehicleId, $driverId, $assignedDate, $endDate = null)
    {
        try {
            // Check if vehicle is available
            $vehicle = $this->getVehicleById($vehicleId);
            if ($vehicle['status_id'] != 1) { // Not available
                return ['success' => false, 'message' => 'Vehicle is not available for assignment'];
            }
            
            // Begin transaction
            $this->db->begin_transaction();
            
            // Create assignment
            $query = "INSERT INTO vehicle_assignment (vehicle_id, driver_id, assigned_date, end_date, status_id) 
                      VALUES (?, ?, ?, ?, 1)"; // Status 1 = Active
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iiss", $vehicleId, $driverId, $assignedDate, $endDate);
            $stmt->execute();
            
            $assignmentId = $this->db->insert_id;
            
            // Update vehicle status to "In Use" (status_id = 2)
            $this->updateVehicleStatus($vehicleId, 2);
            
            $this->db->commit();
            return ['success' => true, 'assignment_id' => $assignmentId];
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error assigning vehicle: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * End a vehicle assignment
     * 
     * @param int $assignmentId Assignment ID
     * @param string $endDate End date
     * @return array Success/failure with message
     */
    public function endAssignment($assignmentId, $endDate)
    {
        try {
            // Begin transaction
            $this->db->begin_transaction();
            
            // Get assignment details to find vehicle ID
            $query = "SELECT vehicle_id FROM vehicle_assignment WHERE assignment_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $assignmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $assignment = $result->fetch_assoc();
            
            if (!$assignment) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Assignment not found'];
            }
            
            // Update assignment status to completed (status_id = 4)
            $query = "UPDATE vehicle_assignment 
                      SET status_id = 4, end_date = ? 
                      WHERE assignment_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("si", $endDate, $assignmentId);
            $stmt->execute();
            
            // Set vehicle back to Available (status_id = 1)
            $this->updateVehicleStatus($assignment['vehicle_id'], 1);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Assignment ended successfully'];
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error ending assignment: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get vehicle reservations
     * 
     * @param int $vehicleId Vehicle ID
     * @return array Reservations
     */
    public function getVehicleReservations($vehicleId)
    {
        try {
            $query = "SELECT r.*, a.full_name as applicant_name, s.status_name
                      FROM reservation r
                      JOIN applicant a ON r.applicant_id = a.applicant_id
                      JOIN reservation_status s ON r.status_id = s.status_id
                      WHERE r.vehicle_id = ?
                      ORDER BY r.date_of_use DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $vehicleId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reservations = [];
            while ($row = $result->fetch_assoc()) {
                $reservations[] = $row;
            }
            
            return $reservations;
        } catch (\Exception $e) {
            error_log("Error fetching vehicle reservations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if a plate number already exists
     * 
     * @param string $plateNo Plate number to check
     * @return bool True if exists
     */
    private function plateNumberExists($plateNo)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM vehicle WHERE plate_no = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("s", $plateNo);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        } catch (\Exception $e) {
            error_log("Error checking plate number: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a plate number exists on vehicles other than specified ID
     * 
     * @param string $plateNo Plate number to check
     * @param int $vehicleId Vehicle ID to exclude
     * @return bool True if exists on another vehicle
     */
    private function plateNumberExistsExcept($plateNo, $vehicleId)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM vehicle WHERE plate_no = ? AND vehicle_id != ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("si", $plateNo, $vehicleId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        } catch (\Exception $e) {
            error_log("Error checking plate number: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if vehicle has active assignments
     * 
     * @param int $vehicleId Vehicle ID
     * @return bool True if vehicle has assignments
     */
    private function vehicleHasAssignments($vehicleId)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM vehicle_assignment 
                      WHERE vehicle_id = ? AND status_id IN (1, 2)"; // Active or Pending
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $vehicleId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        } catch (\Exception $e) {
            error_log("Error checking vehicle assignments: " . $e->getMessage());
            return true; // Assume it has assignments to be safe
        }
    }

    /**
     * Check if vehicle has active reservations
     * 
     * @param int $vehicleId Vehicle ID
     * @return bool True if vehicle has reservations
     */
    private function vehicleHasReservations($vehicleId)
    {
        try {
            $query = "SELECT COUNT(*) as count FROM reservation 
                      WHERE vehicle_id = ? AND status_id IN (1, 2)"; // Pending or Approved
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $vehicleId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        } catch (\Exception $e) {
            error_log("Error checking vehicle reservations: " . $e->getMessage());
            return true; // Assume it has reservations to be safe
        }
    }
    
    /**
     * Search vehicles by criteria
     * 
     * @param string $keyword Search keyword for plate number or type
     * @param int|null $statusId Optional status filter
     * @return array Matching vehicles
     */
    public function searchVehicles($keyword = '', $statusId = null)
    {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            // Build WHERE clause conditionally
            if (!empty($keyword)) {
                $conditions[] = "(v.plate_no LIKE ? OR v.type_of_vehicle LIKE ?)";
                $params[] = "%$keyword%";
                $params[] = "%$keyword%";
                $types .= "ss";
            }
            
            if ($statusId !== null) {
                $conditions[] = "v.status_id = ?";
                $params[] = $statusId;
                $types .= "i";
            }
            
            $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $query = "SELECT v.*, s.status_name 
                      FROM vehicle v 
                      JOIN vehicle_status s ON v.status_id = s.status_id
                      $whereClause
                      ORDER BY v.vehicle_id";
            
            $stmt = $this->db->prepare($query);
            
            // Bind parameters if any
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vehicles = [];
            while ($row = $result->fetch_assoc()) {
                $vehicles[] = $row;
            }
            
            return $vehicles;
        } catch (\Exception $e) {
            error_log("Error searching vehicles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get vehicles available on a specific date (no conflicting reservations)
     * 
     * @param string $date Date to check in Y-m-d format
     * @param string $startTime Start time in H:i format
     * @param string $endTime End time in H:i format
     * @return array Available vehicles
     */
    public function getVehiclesAvailableOn($date, $startTime, $endTime)
    {
        try {
            // Get vehicles that are available and not reserved for the specified time
            $query = "SELECT v.*, s.status_name 
                      FROM vehicle v 
                      JOIN vehicle_status s ON v.status_id = s.status_id
                      WHERE v.status_id = 1 
                      AND v.vehicle_id NOT IN (
                          SELECT r.vehicle_id
                          FROM reservation r
                          WHERE r.date_of_use = ?
                          AND r.status_id IN (1, 2) -- Pending or Approved
                          AND (
                              (r.departure_time <= ? AND r.return_time >= ?)
                          )
                      )
                      ORDER BY v.vehicle_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sss", $date, $endTime, $startTime);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vehicles = [];
            while ($row = $result->fetch_assoc()) {
                $vehicles[] = $row;
            }
            
            return $vehicles;
        } catch (\Exception $e) {
            error_log("Error checking available vehicles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate a vehicle usage report for a specific period
     * 
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return array Report data
     */
    public function generateVehicleUsageReport($startDate, $endDate)
    {
        try {
            $query = "SELECT v.vehicle_id, v.plate_no, v.type_of_vehicle,
                      COUNT(r.reservation_id) as total_trips,
                      IFNULL(SUM(TIME_TO_SEC(TIMEDIFF(r.return_time, r.departure_time)) / 3600), 0) as total_hours
                      FROM vehicle v
                      LEFT JOIN reservation r ON v.vehicle_id = r.vehicle_id
                          AND r.date_of_use BETWEEN ? AND ?
                          AND r.status_id = 4 -- Completed reservations only
                      GROUP BY v.vehicle_id, v.plate_no, v.type_of_vehicle
                      ORDER BY total_trips DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ss", $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reportData = [];
            while ($row = $result->fetch_assoc()) {
                $reportData[] = $row;
            }
            
            return $reportData;
        } catch (\Exception $e) {
            error_log("Error generating vehicle usage report: " . $e->getMessage());
            return [];
        }
    }
}