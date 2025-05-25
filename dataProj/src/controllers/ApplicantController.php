<?php

class ApplicantController {
    private $db;

    public function __construct() {
        // Get database connection
        $this->db = require_once __DIR__ . '/../config/database.php';
        
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Get all applicant profiles for the current user
     * 
     * @return array Applicant profiles
     */
    public function getAllApplicantsForUser($userId = null) {
        // If no user ID is provided, use the logged-in user's ID
        if ($userId === null) {
            if (!isset($_SESSION['user_id'])) {
                return [];
            }
            $userId = $_SESSION['user_id'];
        }

        $stmt = $this->db->prepare("SELECT * FROM applicant WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $applicants = [];
        while ($row = $result->fetch_assoc()) {
            $applicants[] = $row;
        }
        
        return $applicants;
    }

    /**
     * Get a specific applicant profile by ID
     * 
     * @param int $applicantId The applicant ID to retrieve
     * @return array|false The applicant data or false if not found
     */
    public function getApplicantById($applicantId) {
        $stmt = $this->db->prepare("SELECT * FROM applicant WHERE applicant_id = ?");
        $stmt->bind_param("i", $applicantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }

    /**
     * Create a new applicant profile
     * 
     * @param array $data Applicant data
     * @return int|false The ID of the newly created applicant or false on failure
     */
    public function createApplicant($data) {
        // Validate user is logged in
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Validate required fields
        $requiredFields = ['full_name', 'organization_department', 'position', 'contact_no', 'email'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        
        // Create the applicant
        $stmt = $this->db->prepare("
            INSERT INTO applicant (
                full_name, 
                organization_department, 
                position, 
                contact_no, 
                email, 
                user_id
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "sssssi", 
            $data['full_name'], 
            $data['organization_department'], 
            $data['position'], 
            $data['contact_no'], 
            $data['email'], 
            $_SESSION['user_id']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }

    /**
     * Update an existing applicant profile
     * 
     * @param int $applicantId The applicant ID to update
     * @param array $data Updated applicant data
     * @return bool Success or failure
     */
    public function updateApplicant($applicantId, $data) {
        // Validate user is authorized to update this applicant
        $applicant = $this->getApplicantById($applicantId);
        if (!$applicant || $applicant['user_id'] != $_SESSION['user_id']) {
            // Not found or not authorized
            return false;
        }
        
        // Validate required fields
        $requiredFields = ['full_name', 'organization_department', 'position', 'contact_no', 'email'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        
        // Update the applicant
        $stmt = $this->db->prepare("
            UPDATE applicant SET
                full_name = ?,
                organization_department = ?,
                position = ?,
                contact_no = ?,
                email = ?
            WHERE applicant_id = ?
        ");
        
        $stmt->bind_param(
            "sssssi", 
            $data['full_name'], 
            $data['organization_department'], 
            $data['position'], 
            $data['contact_no'], 
            $data['email'], 
            $applicantId
        );
        
        return $stmt->execute();
    }

    /**
     * Delete an applicant profile
     * 
     * @param int $applicantId The applicant ID to delete
     * @return bool Success or failure
     */
    public function deleteApplicant($applicantId) {
        // Validate user is authorized to delete this applicant
        $applicant = $this->getApplicantById($applicantId);
        if (!$applicant || $applicant['user_id'] != $_SESSION['user_id']) {
            // Not found or not authorized
            return false;
        }
        
        // Check if this applicant has any reservations
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM reservation WHERE applicant_id = ?");
        $stmt->bind_param("i", $applicantId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            // Cannot delete applicant with reservations
            return false;
        }
        
        // Delete the applicant
        $stmt = $this->db->prepare("DELETE FROM applicant WHERE applicant_id = ?");
        $stmt->bind_param("i", $applicantId);
        
        return $stmt->execute();
    }

    /**
     * Check if the current user owns a specific applicant profile
     * 
     * @param int $applicantId The applicant ID to check
     * @return bool True if the user owns this applicant
     */
    public function userOwnsApplicant($applicantId) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM applicant 
            WHERE applicant_id = ? AND user_id = ?
        ");
        $stmt->bind_param("ii", $applicantId, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }

    /**
     * Count reservations for a specific applicant
     * 
     * @param int $applicantId The applicant ID
     * @return int The number of reservations
     */
    public function countReservations($applicantId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM reservation WHERE applicant_id = ?");
        $stmt->bind_param("i", $applicantId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }

    /**
     * Get reservations for a specific applicant
     * 
     * @param int $applicantId The applicant ID
     * @return array The reservations
     */
    public function getApplicantReservations($applicantId) {
        $stmt = $this->db->prepare("
            SELECT r.*, v.plate_no, v.type_of_vehicle, rs.status_name
            FROM reservation r
            JOIN vehicle v ON r.vehicle_id = v.vehicle_id
            JOIN reservation_status rs ON r.status_id = rs.status_id
            WHERE r.applicant_id = ?
            ORDER BY r.date_of_use DESC
        ");
        $stmt->bind_param("i", $applicantId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        
        return $reservations;
    }

    /**
     * Handle form submission for creating a new applicant
     */
    public function handleCreateForm() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $applicantData = [
                'full_name' => $_POST['full_name'] ?? '',
                'organization_department' => $_POST['organization_department'] ?? '',
                'position' => $_POST['position'] ?? '',
                'contact_no' => $_POST['contact_no'] ?? '',
                'email' => $_POST['email'] ?? ''
            ];
            
            $applicantId = $this->createApplicant($applicantData);
            
            if ($applicantId) {
                // Redirect to applicant profiles page with success message
                $_SESSION['success_message'] = "Applicant profile created successfully!";
                header("Location: ../views/user/applicant/applicant-profiles.php");
                exit;
            } else {
                // Redirect back to the form with error message
                $_SESSION['error_message'] = "Failed to create applicant profile. Please check all fields.";
                header("Location: ../views/user/applicant/create_applicant.php");
                exit;
            }
        }
    }

    /**
     * Handle form submission for updating an applicant
     * 
     * @param int $applicantId The applicant ID to update
     */
    public function handleUpdateForm($applicantId) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $applicantData = [
                'full_name' => $_POST['full_name'] ?? '',
                'organization_department' => $_POST['organization_department'] ?? '',
                'position' => $_POST['position'] ?? '',
                'contact_no' => $_POST['contact_no'] ?? '',
                'email' => $_POST['email'] ?? ''
            ];
            
            $success = $this->updateApplicant($applicantId, $applicantData);
            
            if ($success) {
                // Redirect to applicant profiles page with success message
                $_SESSION['success_message'] = "Applicant profile updated successfully!";
                header("Location: ../views/user/applicant/applicant-profiles.php");
                exit;
            } else {
                // Redirect back to the form with error message
                $_SESSION['error_message'] = "Failed to update applicant profile. Please check all fields.";
                header("Location: ../views/user/applicant/edit_applicant.php?id=" . $applicantId);
                exit;
            }
        }
    }

    /**
     * Handle applicant deletion
     * 
     * @param int $applicantId The applicant ID to delete
     */
    public function handleDelete($applicantId) {
        if (!$this->userOwnsApplicant($applicantId)) {
            $_SESSION['error_message'] = "You do not have permission to delete this applicant profile.";
            header("Location: ../views/user/applicant/applicant-profiles.php");
            exit;
        }
        
        if ($this->countReservations($applicantId) > 0) {
            $_SESSION['error_message'] = "Cannot delete applicant profile with existing reservations.";
            header("Location: ../views/user/applicant/applicant-profiles.php");
            exit;
        }
        
        $success = $this->deleteApplicant($applicantId);
        
        if ($success) {
            $_SESSION['success_message'] = "Applicant profile deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete applicant profile.";
        }
        
        header("Location: ../views/user/applicant/applicant-profiles.php");
        exit;
    }
}