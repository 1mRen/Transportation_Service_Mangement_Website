<?php
// src/controllers/ReservationController.php

require_once __DIR__ . '/../config/database.php';

class ReservationController {
    private $db;
    // Constants for status IDs
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELLED = 4;
    
    // Constants for vehicle status
    const VEHICLE_AVAILABLE = 1;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Display a listing of reservations for the current user
     * @return array Reservations data
     * @throws Exception On database error
     */
    public function listReservations() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /src/views/auth/signin.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'] ?? 'Applicant';
        
        try {
            $conn = $this->db->getConnection();
            
            if ($userRole === 'Admin') {
                // Admin sees all reservations
                $query = "SELECT r.*, a.full_name, v.plate_no, rs.status_name
                         FROM reservation r
                         JOIN applicant a ON r.applicant_id = a.applicant_id
                         JOIN vehicle v ON r.vehicle_id = v.vehicle_id
                         JOIN reservation_status rs ON r.status_id = rs.status_id
                         ORDER BY r.date_of_use DESC";
                $stmt = $conn->prepare($query);
            } else {
                // User sees only their reservations (across all their applicant profiles)
                $query = "SELECT r.*, a.full_name, v.plate_no, rs.status_name
                         FROM reservation r
                         JOIN applicant a ON r.applicant_id = a.applicant_id
                         JOIN vehicle v ON r.vehicle_id = v.vehicle_id
                         JOIN reservation_status rs ON r.status_id = rs.status_id
                         WHERE a.user_id = ?
                         ORDER BY r.date_of_use DESC";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $userId);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $reservations = [];
            
            while ($row = $result->fetch_assoc()) {
                $reservations[] = $row;
            }
            
            $stmt->close();
            
            return $reservations;
        } catch (Exception $e) {
            error_log("Error in listReservations: " . $e->getMessage());
            throw new Exception("Unable to retrieve reservations. Please try again later.");
        }
    }

    /**
     * Display user's applicant profiles
     * @return array Applicant profiles data
     * @throws Exception On database error
     */
    public function listApplicantProfiles() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /src/views/auth/signin.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        try {
            $conn = $this->db->getConnection();
            
            $query = "SELECT * FROM applicant WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $applicants = [];
            
            while ($row = $result->fetch_assoc()) {
                $applicants[] = $row;
            }
            
            $stmt->close();
            
            return $applicants;
        } catch (Exception $e) {
            error_log("Error in listApplicantProfiles: " . $e->getMessage());
            throw new Exception("Unable to retrieve applicant profiles. Please try again later.");
        }
    }

    /**
     * Get details for specific applicant
     * @param int $applicantId ID of the applicant
     * @return array|null Applicant data or null if not found/unauthorized
     * @throws Exception On database error
     */
    public function getApplicant($applicantId) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /src/views/auth/signin.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        try {
            $conn = $this->db->getConnection();
            
            // Check if this applicant belongs to the user or user is admin
            if ($_SESSION['role'] !== 'Admin') {
                $query = "SELECT * FROM applicant WHERE applicant_id = ? AND user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $applicantId, $userId);
            } else {
                $query = "SELECT * FROM applicant WHERE applicant_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $applicantId);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                return null;
            }
            
            $applicant = $result->fetch_assoc();
            $stmt->close();
            
            return $applicant;
        } catch (Exception $e) {
            error_log("Error in getApplicant: " . $e->getMessage());
            throw new Exception("Unable to retrieve applicant details. Please try again later.");
        }
    }

    /**
     * Create a new applicant profile
     * @param string $fullName Full name of applicant
     * @param string $organizationDepartment Organization or department
     * @param string $position Job position
     * @param string $contactNo Contact number
     * @param string $email Email address
     * @return int New applicant ID or 0 on failure
     * @throws Exception On database error
     */
    public function createApplicant($fullName, $organizationDepartment, $position, $contactNo, $email) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /src/views/auth/signin.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();
            
            $query = "INSERT INTO applicant (
                        full_name, 
                        organization_department, 
                        position, 
                        contact_no, 
                        email, 
                        user_id
                    ) VALUES (?, ?, ?, ?, ?, ?)";
                    
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", 
                $fullName, 
                $organizationDepartment, 
                $position, 
                $contactNo, 
                $email, 
                $userId
            );
            
            $stmt->execute();
            $applicantId = $conn->insert_id;
            $stmt->close();
            
            $conn->commit();
            return $applicantId;
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error in createApplicant: " . $e->getMessage());
            throw new Exception("Unable to create applicant profile. Please try again later.");
        }
    }

    /**
     * Update an existing applicant profile
     * @param int $applicantId ID of applicant to update
     * @param string $fullName Updated full name
     * @param string $organizationDepartment Updated organization/department
     * @param string $position Updated position
     * @param string $contactNo Updated contact number
     * @param string $email Updated email address
     * @return bool Success or failure
     * @throws Exception On database error or unauthorized access
     */
    public function updateApplicant($applicantId, $fullName, $organizationDepartment, $position, $contactNo, $email) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /src/views/auth/signin.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();
            
            // Check if this applicant belongs to the user or user is admin
            if ($_SESSION['role'] !== 'Admin') {
                $checkQuery = "SELECT * FROM applicant WHERE applicant_id = ? AND user_id = ?";
                $checkStmt = $conn->prepare($checkQuery);
                $checkStmt->bind_param("ii", $applicantId, $userId);
                $checkStmt->execute();
                
                if ($checkStmt->get_result()->num_rows === 0) {
                    $checkStmt->close();
                    throw new Exception("Unauthorized access to applicant profile");
                }
                $checkStmt->close();
            }
            
            $query = "UPDATE applicant SET 
                        full_name = ?, 
                        organization_department = ?, 
                        position = ?, 
                        contact_no = ?, 
                        email = ?, 
                        updated_at = CURRENT_TIMESTAMP
                      WHERE applicant_id = ?";
                      
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", 
                $fullName, 
                $organizationDepartment, 
                $position, 
                $contactNo, 
                $email, 
                $applicantId
            );
            
            $stmt->execute();
            $result = $stmt->affected_rows > 0;
            $stmt->close();
            
            $conn->commit();
            return $result;
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error in updateApplicant: " . $e->getMessage());
            if (strpos($e->getMessage(), "Unauthorized") !== false) {
                throw $e; // Re-throw authorization errors
            }
            throw new Exception("Unable to update applicant profile. Please try again later.");
        }
    }

    /**
     * Create a new reservation
     * @param int $applicantId Applicant ID
     * @param int $vehicleId Vehicle ID
     * @param string $dateOfUse Date of use (YYYY-MM-DD)
     * @param string $departureArea Departure area
     * @param string $destination Destination
     * @param string $departureTime Departure time (HH:MM:SS)
     * @param string $returnTime Return time (HH:MM:SS)
     * @param string $purpose Purpose of reservation
     * @return int|bool New reservation ID or false on failure
     * @throws Exception On database error or unauthorized access
     */
    public function createReservation($applicantId, $vehicleId, $dateOfUse, $departureArea, $destination, 
                                     $departureTime, $returnTime, $purpose) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /src/views/auth/signin.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $conn = $this->db->getConnection();
        
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Check if this applicant belongs to the user or user is admin
            if ($_SESSION['role'] !== 'Admin') {
                $checkQuery = "SELECT * FROM applicant WHERE applicant_id = ? AND user_id = ?";
                $checkStmt = $conn->prepare($checkQuery);
                $checkStmt->bind_param("ii", $applicantId, $userId);
                $checkStmt->execute();
                
                if ($checkStmt->get_result()->num_rows === 0) {
                    $checkStmt->close();
                    throw new Exception("Unauthorized access to create reservation for this applicant");
                }
                $checkStmt->close();
            }
            
            // Check if vehicle is available on the selected date
            $vehicleCheck = "SELECT v.* FROM vehicle v 
                            WHERE v.vehicle_id = ? AND v.status_id = ? AND v.vehicle_id NOT IN (
                                SELECT r.vehicle_id FROM reservation r 
                                WHERE r.date_of_use = ? AND r.status_id IN (2, 3)
                            )";
            $vehicleStmt = $conn->prepare($vehicleCheck);
            $vehicleStmt->bind_param("iis", $vehicleId, self::VEHICLE_AVAILABLE, $dateOfUse);
            $vehicleStmt->execute();
            
            if ($vehicleStmt->get_result()->num_rows === 0) {
                $vehicleStmt->close();
                throw new Exception("Selected vehicle is not available on the requested date");
            }
            $vehicleStmt->close();
            
            // Default status is 1 (Pending)
            $statusId = self::STATUS_PENDING;
            
            // Insert reservation
            $query = "INSERT INTO reservation (
                        applicant_id,
                        vehicle_id,
                        date_of_use,
                        departure_area,
                        destination,
                        departure_time,
                        return_time,
                        purpose,
                        status_id
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iisssssi", 
                $applicantId,
                $vehicleId,
                $dateOfUse,
                $departureArea,
                $destination,
                $departureTime,
                $returnTime,
                $purpose,
                $statusId
            );
            
            $stmt->execute();
            $reservationId = $conn->insert_id;
            $stmt->close();
            
            // Create initial status history entry
            $historyQuery = "INSERT INTO reserve_status_history (
                              reservation_id,
                              status_id,
                              updated_by
                           ) VALUES (?, ?, ?)";
                           
            $historyStmt = $conn->prepare($historyQuery);
            $historyStmt->bind_param("iii", $reservationId, $statusId, $userId);
            $historyStmt->execute();
            $historyStmt->close();
            
            // Commit transaction
            $conn->commit();
            
            return $reservationId;
            
        } catch (Exception $e) {
            // Roll back on error
            $conn->rollback();
            error_log("Error in createReservation: " . $e->getMessage());
            throw $e; // Re-throw the exception with its original message
        }
    }

    /**
     * Get available vehicles for a specific date
     * @param string|null $date Date to check availability (YYYY-MM-DD)
     * @return array Available vehicles
     * @throws Exception On database error
     */
    public function getAvailableVehicles($date = null) {
        try {
            $conn = $this->db->getConnection();
            
            // If date is provided, check availability on that specific date
            // Otherwise, just get vehicles with status "Available"
            if ($date) {
                // More complex query to check availability based on existing reservations
                $query = "SELECT v.* 
                         FROM vehicle v
                         WHERE v.status_id = ? AND v.vehicle_id NOT IN (
                            SELECT r.vehicle_id 
                            FROM reservation r 
                            WHERE r.date_of_use = ? AND r.status_id IN (2, 3) 
                         )";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("is", self::VEHICLE_AVAILABLE, $date);
            } else {
                $query = "SELECT * FROM vehicle WHERE status_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", self::VEHICLE_AVAILABLE);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $vehicles = [];
            
            while ($row = $result->fetch_assoc()) {
                $vehicles[] = $row;
            }
            
            $stmt->close();
            
            return $vehicles;
        } catch (Exception $e) {
            error_log("Error in getAvailableVehicles: " . $e->getMessage());
            throw new Exception("Unable to retrieve available vehicles. Please try again later.");
        }
    }

    /**
     * Get reservation details including documents and status history
     * @param int $reservationId Reservation ID
     * @return array|null Reservation details or null if not found/unauthorized
     * @throws Exception On database error
     */
    public function getReservation($reservationId) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /src/views/auth/signin.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        try {
            $conn = $this->db->getConnection();
            
            // Query to get reservation with related data
            $query = "SELECT r.*, a.*, v.*, rs.status_name
                     FROM reservation r
                     JOIN applicant a ON r.applicant_id = a.applicant_id
                     JOIN vehicle v ON r.vehicle_id = v.vehicle_id
                     JOIN reservation_status rs ON r.status_id = rs.status_id
                     WHERE r.reservation_id = ?";
                     
            // If user is not admin, only show their own reservations
            if ($_SESSION['role'] !== 'Admin') {
                $query .= " AND a.user_id = ?";
            }
            
            $stmt = $conn->prepare($query);
            
            if ($_SESSION['role'] !== 'Admin') {
                $stmt->bind_param("ii", $reservationId, $userId);
            } else {
                $stmt->bind_param("i", $reservationId);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                return null;
            }
            
            $reservation = $result->fetch_assoc();
            $stmt->close();
            
            // Get documents for this reservation
            $reservation['documents'] = $this->getReservationDocuments($reservationId);
            
            // Get status history
            $reservation['status_history'] = $this->getReservationStatusHistory($reservationId);
            
            return $reservation;
        } catch (Exception $e) {
            error_log("Error in getReservation: " . $e->getMessage());
            throw new Exception("Unable to retrieve reservation details. Please try again later.");
        }
    }

    /**
     * Get documents for a reservation
     * @param int $reservationId Reservation ID
     * @return array Documents data
     * @throws Exception On database error
     */
    public function getReservationDocuments($reservationId) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "SELECT rd.*, u.name as uploaded_by_name
                     FROM reservation_documents rd
                     JOIN users u ON rd.uploaded_by = u.user_id
                     WHERE rd.reservation_id = ?
                     ORDER BY rd.upload_date DESC";
                     
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $reservationId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $documents = [];
            
            while ($row = $result->fetch_assoc()) {
                $documents[] = $row;
            }
            
            $stmt->close();
            
            return $documents;
        } catch (Exception $e) {
            error_log("Error in getReservationDocuments: " . $e->getMessage());
            throw new Exception("Unable to retrieve reservation documents. Please try again later.");
        }
    }

    /**
     * Get status history for a reservation
     * @param int $reservationId Reservation ID
     * @return array Status history data
     * @throws Exception On database error
     */
    public function getReservationStatusHistory($reservationId) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "SELECT rsh.*, rs.status_name, u.name as updated_by_name
                     FROM reserve_status_history rsh
                     JOIN reservation_status rs ON rsh.status_id = rs.status_id
                     JOIN users u ON rsh.updated_by = u.user_id
                     WHERE rsh.reservation_id = ?
                     ORDER BY rsh.timestamp DESC";
                     
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $reservationId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $history = [];
            
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
            
            $stmt->close();
            
            return $history;
        } catch (Exception $e) {
            error_log("Error in getReservationStatusHistory: " . $e->getMessage());
            throw new Exception("Unable to retrieve status history. Please try again later.");
        }
    }

    /**
     * Update reservation status (Admin only)
     * @param int $reservationId Reservation ID
     * @param int $statusId New status ID
     * @return bool Success or failure
     * @throws Exception On database error or unauthorized access 
     */
    public function updateReservationStatus($reservationId, $statusId) {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
            throw new Exception("Unauthorized access. Admin privileges required.");
        }

        $userId = $_SESSION['user_id'];
        $conn = $this->db->getConnection();
        
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Check if reservation exists
            $checkQuery = "SELECT reservation_id FROM reservation WHERE reservation_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("i", $reservationId);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows === 0) {
                $checkStmt->close();
                throw new Exception("Reservation not found");
            }
            $checkStmt->close();
            
            // Update reservation status
            $query = "UPDATE reservation SET status_id = ? WHERE reservation_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $statusId, $reservationId);
            $stmt->execute();
            $stmt->close();
            
            // Add to status history
            $historyQuery = "INSERT INTO reserve_status_history (
                              reservation_id,
                              status_id,
                              updated_by
                           ) VALUES (?, ?, ?)";
                           
            $historyStmt = $conn->prepare($historyQuery);
            $historyStmt->bind_param("iii", $reservationId, $statusId, $userId);
            $historyStmt->execute();
            $historyStmt->close();
            
            // Commit transaction
            $conn->commit();
            
            return true;
            
        } catch (Exception $e) {
            // Roll back on error
            $conn->rollback();
            error_log("Error in updateReservationStatus: " . $e->getMessage());
            throw $e; // Re-throw with original message
        }
    }

    /**
     * Upload documents for a reservation
     * @param int $reservationId Reservation ID
     * @param array $files Files array from $_FILES
     * @param string|null $notes Optional notes
     * @return array|bool Array of uploaded files or false on failure
     * @throws Exception On database error or unauthorized access
     */
    public function uploadReservationDocuments($reservationId, $files, $notes = null) {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Authentication required");
        }

        $userId = $_SESSION['user_id'];
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();
            
            // Check if this reservation belongs to the user or user is admin
            $canAccess = false;
            
            if ($_SESSION['role'] === 'Admin') {
                $canAccess = true;
            } else {
                $checkQuery = "SELECT r.* FROM reservation r 
                              JOIN applicant a ON r.applicant_id = a.applicant_id
                              WHERE r.reservation_id = ? AND a.user_id = ?";
                $checkStmt = $conn->prepare($checkQuery);
                $checkStmt->bind_param("ii", $reservationId, $userId);
                $checkStmt->execute();
                
                if ($checkStmt->get_result()->num_rows > 0) {
                    $canAccess = true;
                }
                $checkStmt->close();
            }
            
            if (!$canAccess) {
                throw new Exception("Unauthorized access to this reservation");
            }
            
            // Process each file
            $uploadedFiles = [];
            $uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . '/uploads/reservation_documents/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDirectory)) {
                if (!mkdir($uploadDirectory, 0777, true)) {
                    throw new Exception("Failed to create upload directory");
                }
            }
            
            foreach ($files['name'] as $key => $name) {
                if ($files['error'][$key] === 0) {
                    $tmpName = $files['tmp_name'][$key];
                    $fileSize = $files['size'][$key];
                    $fileType = pathinfo($name, PATHINFO_EXTENSION);
                    
                    // Generate unique filename
                    $newFileName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-]/', '_', $name);
                    $filePath = $uploadDirectory . $newFileName;
                    
                    // Move uploaded file
                    if (move_uploaded_file($tmpName, $filePath)) {
                        // Save to database
                        $dbFilePath = '/uploads/reservation_documents/' . $newFileName;
                        
                        $query = "INSERT INTO reservation_documents (
                                    reservation_id,
                                    document_name,
                                    document_type,
                                    file_path,
                                    file_size,
                                    uploaded_by,
                                    notes
                                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
                                
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("isssiss", 
                            $reservationId,
                            $name,
                            $fileType,
                            $dbFilePath,
                            $fileSize,
                            $userId,
                            $notes
                        );
                        
                        if ($stmt->execute()) {
                            $uploadedFiles[] = [
                                'document_id' => $conn->insert_id,
                                'name' => $name,
                                'path' => $dbFilePath
                            ];
                        } else {
                            // If database insert fails, we need to remove the uploaded file
                            unlink($filePath);
                            throw new Exception("Failed to save document information");
                        }
                        
                        $stmt->close();
                    } else {
                        throw new Exception("Failed to upload file: " . $name);
                    }
                } else if ($files['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                    // Log upload errors except when no file was uploaded
                    error_log("File upload error: " . $files['error'][$key] . " for file " . $files['name'][$key]);
                }
            }
            
            $conn->commit();
            return $uploadedFiles;
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error in uploadReservationDocuments: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a document
     * @param int $documentId Document ID
     * @return bool Success or failure
     * @throws Exception On database error or unauthorized access
     */
    public function deleteDocument($documentId) {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("Authentication required");
        }

        $userId = $_SESSION['user_id'];
        $conn = $this->db->getConnection();
        
        try {
            $conn->begin_transaction();
            
            // Get document details first
            $query = "SELECT rd.*, r.applicant_id 
                     FROM reservation_documents rd
                     JOIN reservation r ON rd.reservation_id = r.reservation_id
                     WHERE rd.document_id = ?";
                     
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $documentId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                throw new Exception("Document not found");
            }
            
            $document = $result->fetch_assoc();
            $stmt->close();
            
            // Check authorization
            $canDelete = false;
            
            if ($_SESSION['role'] === 'Admin') {
                $canDelete = true;
            } else if ($document['uploaded_by'] === $userId) {
                // User uploaded this document
                $checkQuery = "SELECT a.* FROM applicant a 
                              JOIN reservation r ON a.applicant_id = r.applicant_id
                              JOIN reservation_documents rd ON r.reservation_id = rd.reservation_id
                              WHERE rd.document_id = ? AND a.user_id = ?";
                $checkStmt = $conn->prepare($checkQuery);
                $checkStmt->bind_param("ii", $documentId, $userId);
                $checkStmt->execute();
                
                if ($checkStmt->get_result()->num_rows > 0) {
                    $canDelete = true;
                }
                $checkStmt->close();
            }
            
            if (!$canDelete) {
                throw new Exception("Unauthorized access to delete this document");
            }
            
            // Delete the file
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $document['file_path'];
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    error_log("Could not delete file: " . $filePath);
                    // Continue with database deletion even if file removal fails
                }
            }
            
            // Delete from database
            $deleteQuery = "DELETE FROM reservation_documents WHERE document_id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $documentId);
            $deleteStmt->execute();
            $result = $deleteStmt->affected_rows > 0;
            $deleteStmt->close();
            
            if (!$result) {
                throw new Exception("Failed to delete document record");
            }
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error in deleteDocument: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel a reservation (User can cancel their own pending reservations)
     * @param int $reservationId Reservation ID
     * @return bool Success or failure
     * @throws Exception On database error or unauthorized access
     */
    public function cancelReservation($reservationId) {
      if (!isset($_SESSION['user_id'])) {
          throw new Exception("Authentication required");
      }

      $userId = $_SESSION['user_id'];
      $conn = $this->db->getConnection();
      
      try {
          $conn->begin_transaction();
          
          // Check if this reservation belongs to the user and is in pending status
          $canCancel = false;
          
          if ($_SESSION['role'] === 'Admin') {
              // Admin can cancel any reservation
              $checkQuery = "SELECT * FROM reservation WHERE reservation_id = ?";
              $checkStmt = $conn->prepare($checkQuery);
              $checkStmt->bind_param("i", $reservationId);
          } else {
              // Regular users can only cancel their own pending reservations
              $checkQuery = "SELECT r.* FROM reservation r 
                            JOIN applicant a ON r.applicant_id = a.applicant_id
                            WHERE r.reservation_id = ? AND a.user_id = ? AND r.status_id = ?";
              $checkStmt = $conn->prepare($checkQuery);
              $checkStmt->bind_param("iii", $reservationId, $userId, self::STATUS_PENDING);
          }
          
          $checkStmt->execute();
          $result = $checkStmt->get_result();
          
          if ($result->num_rows > 0) {
              $canCancel = true;
          }
          $checkStmt->close();
          
          if (!$canCancel) {
              throw new Exception("You cannot cancel this reservation. It may not be in pending status or may not belong to you.");
          }
          
          // Update reservation status to cancelled
          $query = "UPDATE reservation SET status_id = ? WHERE reservation_id = ?";
          $stmt = $conn->prepare($query);
          $statusId = self::STATUS_CANCELLED;
          $stmt->bind_param("ii", $statusId, $reservationId);
          $stmt->execute();
          $stmt->close();
          
          // Add to status history
          $historyQuery = "INSERT INTO reserve_status_history (
                            reservation_id,
                            status_id,
                            updated_by
                         ) VALUES (?, ?, ?)";
                         
          $historyStmt = $conn->prepare($historyQuery);
          $historyStmt->bind_param("iii", $reservationId, $statusId, $userId);
          $historyStmt->execute();
          $historyStmt->close();
          
          // Commit transaction
          $conn->commit();
          
          return true;
          
      } catch (Exception $e) {
          // Roll back on error
          $conn->rollback();
          error_log("Error in cancelReservation: " . $e->getMessage());
          throw $e; // Re-throw with original message
      }
  }

  /**
   * Get summary statistics for the dashboard (Admin only)
   * @return array Statistics data
   * @throws Exception On database error or unauthorized access
   */
  public function getDashboardStats() {
      if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
          throw new Exception("Unauthorized access. Admin privileges required.");
      }
      
      try {
          $conn = $this->db->getConnection();
          $stats = [];
          
          // Total reservations
          $query = "SELECT COUNT(*) as total FROM reservation";
          $result = $conn->query($query);
          $stats['total_reservations'] = $result->fetch_assoc()['total'];
          
          // Reservations by status
          $query = "SELECT rs.status_name, COUNT(r.reservation_id) as count 
                   FROM reservation r
                   JOIN reservation_status rs ON r.status_id = rs.status_id
                   GROUP BY r.status_id
                   ORDER BY count DESC";
          $result = $conn->query($query);
          
          $stats['by_status'] = [];
          while ($row = $result->fetch_assoc()) {
              $stats['by_status'][] = $row;
          }
          
          // Reservations this month
          $query = "SELECT COUNT(*) as total FROM reservation 
                   WHERE MONTH(date_of_use) = MONTH(CURRENT_DATE())
                   AND YEAR(date_of_use) = YEAR(CURRENT_DATE())";
          $result = $conn->query($query);
          $stats['reservations_this_month'] = $result->fetch_assoc()['total'];
          
          // Vehicle usage
          $query = "SELECT v.plate_no, COUNT(r.reservation_id) as usage_count 
                   FROM vehicle v
                   LEFT JOIN reservation r ON v.vehicle_id = r.vehicle_id
                   GROUP BY v.vehicle_id
                   ORDER BY usage_count DESC";
          $result = $conn->query($query);
          
          $stats['vehicle_usage'] = [];
          while ($row = $result->fetch_assoc()) {
              $stats['vehicle_usage'][] = $row;
          }
          
          return $stats;
          
      } catch (Exception $e) {
          error_log("Error in getDashboardStats: " . $e->getMessage());
          throw new Exception("Unable to retrieve dashboard statistics. Please try again later.");
      }
  }

  /**
   * Search reservations by various criteria
   * @param array $criteria Search criteria (key-value pairs)
   * @return array Matching reservations
   * @throws Exception On database error
   */
  public function searchReservations($criteria) {
      if (!isset($_SESSION['user_id'])) {
          header('Location: /src/views/auth/signin.php');
          exit;
      }

      $userId = $_SESSION['user_id'];
      $userRole = $_SESSION['role'] ?? 'Applicant';
      
      try {
          $conn = $this->db->getConnection();
          
          $query = "SELECT r.*, a.full_name, v.plate_no, rs.status_name
                   FROM reservation r
                   JOIN applicant a ON r.applicant_id = a.applicant_id
                   JOIN vehicle v ON r.vehicle_id = v.vehicle_id
                   JOIN reservation_status rs ON r.status_id = rs.status_id
                   WHERE 1=1";
                   
          $params = [];
          $types = "";
          
          // If not admin, restrict to user's reservations
          if ($userRole !== 'Admin') {
              $query .= " AND a.user_id = ?";
              $params[] = $userId;
              $types .= "i";
          }
          
          // Add search criteria
          if (!empty($criteria['applicant_name'])) {
              $query .= " AND a.full_name LIKE ?";
              $params[] = "%" . $criteria['applicant_name'] . "%";
              $types .= "s";
          }
          
          if (!empty($criteria['plate_no'])) {
              $query .= " AND v.plate_no LIKE ?";
              $params[] = "%" . $criteria['plate_no'] . "%";
              $types .= "s";
          }
          
          if (!empty($criteria['status_id'])) {
              $query .= " AND r.status_id = ?";
              $params[] = $criteria['status_id'];
              $types .= "i";
          }
          
          if (!empty($criteria['date_from']) && !empty($criteria['date_to'])) {
              $query .= " AND r.date_of_use BETWEEN ? AND ?";
              $params[] = $criteria['date_from'];
              $params[] = $criteria['date_to'];
              $types .= "ss";
          } else if (!empty($criteria['date_from'])) {
              $query .= " AND r.date_of_use >= ?";
              $params[] = $criteria['date_from'];
              $types .= "s";
          } else if (!empty($criteria['date_to'])) {
              $query .= " AND r.date_of_use <= ?";
              $params[] = $criteria['date_to'];
              $types .= "s";
          }
          
          if (!empty($criteria['destination'])) {
              $query .= " AND r.destination LIKE ?";
              $params[] = "%" . $criteria['destination'] . "%";
              $types .= "s";
          }
          
          // Order by
          $query .= " ORDER BY r.date_of_use DESC";
          
          $stmt = $conn->prepare($query);
          
          if (!empty($params)) {
              $stmt->bind_param($types, ...$params);
          }
          
          $stmt->execute();
          $result = $stmt->get_result();
          $reservations = [];
          
          while ($row = $result->fetch_assoc()) {
              $reservations[] = $row;
          }
          
          $stmt->close();
          
          return $reservations;
          
      } catch (Exception $e) {
          error_log("Error in searchReservations: " . $e->getMessage());
          throw new Exception("Unable to search reservations. Please try again later.");
      }
  }

  /**
   * Get upcoming reservations for dashboard
   * @param int $limit Maximum number of reservations to return
   * @return array Upcoming reservations
   * @throws Exception On database error
   */
  public function getUpcomingReservations($limit = 5) {
      if (!isset($_SESSION['user_id'])) {
          header('Location: /src/views/auth/signin.php');
          exit;
      }

      $userId = $_SESSION['user_id'];
      $userRole = $_SESSION['role'] ?? 'Applicant';
      
      try {
          $conn = $this->db->getConnection();
          $today = date('Y-m-d');
          
          if ($userRole === 'Admin') {
              // Admin sees all upcoming reservations
              $query = "SELECT r.*, a.full_name, v.plate_no, rs.status_name
                       FROM reservation r
                       JOIN applicant a ON r.applicant_id = a.applicant_id
                       JOIN vehicle v ON r.vehicle_id = v.vehicle_id
                       JOIN reservation_status rs ON r.status_id = rs.status_id
                       WHERE r.date_of_use >= ?
                       AND r.status_id = ?
                       ORDER BY r.date_of_use ASC
                       LIMIT ?";
              $stmt = $conn->prepare($query);
              $statusId = self::STATUS_APPROVED;
              $stmt->bind_param("sii", $today, $statusId, $limit);
          } else {
              // User sees only their upcoming reservations
              $query = "SELECT r.*, a.full_name, v.plate_no, rs.status_name
                       FROM reservation r
                       JOIN applicant a ON r.applicant_id = a.applicant_id
                       JOIN vehicle v ON r.vehicle_id = v.vehicle_id
                       JOIN reservation_status rs ON r.status_id = rs.status_id
                       WHERE r.date_of_use >= ?
                       AND r.status_id = ?
                       AND a.user_id = ?
                       ORDER BY r.date_of_use ASC
                       LIMIT ?";
              $stmt = $conn->prepare($query);
              $statusId = self::STATUS_APPROVED;
              $stmt->bind_param("siii", $today, $statusId, $userId, $limit);
          }
          
          $stmt->execute();
          $result = $stmt->get_result();
          $reservations = [];
          
          while ($row = $result->fetch_assoc()) {
              $reservations[] = $row;
          }
          
          $stmt->close();
          
          return $reservations;
          
      } catch (Exception $e) {
          error_log("Error in getUpcomingReservations: " . $e->getMessage());
          throw new Exception("Unable to retrieve upcoming reservations. Please try again later.");
      }
  }

  /**
   * Generate report of reservations for a given period
   * @param string $startDate Start date (YYYY-MM-DD)
   * @param string $endDate End date (YYYY-MM-DD)
   * @param int|null $statusId Optional status filter
   * @return array Report data
   * @throws Exception On database error or unauthorized access
   */
  public function generateReservationReport($startDate, $endDate, $statusId = null) {
      if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
          throw new Exception("Unauthorized access. Admin privileges required.");
      }
      
      try {
          $conn = $this->db->getConnection();
          
          $query = "SELECT r.*, a.full_name, a.organization_department, a.position, 
                   v.plate_no, v.model, rs.status_name
                   FROM reservation r
                   JOIN applicant a ON r.applicant_id = a.applicant_id
                   JOIN vehicle v ON r.vehicle_id = v.vehicle_id
                   JOIN reservation_status rs ON r.status_id = rs.status_id
                   WHERE r.date_of_use BETWEEN ? AND ?";
                   
          $params = [$startDate, $endDate];
          $types = "ss";
          
          if ($statusId !== null) {
              $query .= " AND r.status_id = ?";
              $params[] = $statusId;
              $types .= "i";
          }
          
          $query .= " ORDER BY r.date_of_use ASC";
          
          $stmt = $conn->prepare($query);
          $stmt->bind_param($types, ...$params);
          $stmt->execute();
          
          $result = $stmt->get_result();
          $report = [];
          
          while ($row = $result->fetch_assoc()) {
              $report[] = $row;
          }
          
          $stmt->close();
          
          return $report;
          
      } catch (Exception $e) {
          error_log("Error in generateReservationReport: " . $e->getMessage());
          throw new Exception("Unable to generate report. Please try again later.");
      }
  }
}