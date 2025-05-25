<?php
// src/controllers/UserManagementController.php

class UserManagementController
{
    private $conn;

    public function __construct()
    {
        $this->conn = require __DIR__ . '/../config/database.php';
    }

    // Get all users
    public function index()
    {
        $stmt = $this->conn->prepare("SELECT * FROM users");
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }

    // Get a single user
    public function getUserById($user_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    // Create a new user
    public function create($data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO users (name, email, username, password_hash, role, applicant_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param(
            "sssssi",
            $data['name'],
            $data['email'],
            $data['username'],
            $password_hash,
            $data['role'],
            $data['applicant_id']
        );

        return $stmt->execute();
    }

    // Update an existing user
    public function update($user_id, $data)
    {
        $stmt = $this->conn->prepare("
            UPDATE users SET name = ?, email = ?, username = ?, role = ?, updated_at = NOW()
            WHERE user_id = ?
        ");

        $stmt->bind_param(
            "ssssi",
            $data['name'],
            $data['email'],
            $data['username'],
            $data['role'],
            $user_id
        );

        return $stmt->execute();
    }

    // Delete a user
    public function delete($user_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    /**
     * Upload user profile picture
     * 
     * @param array $file File data from $_FILES
     * @return string|false Path to uploaded file or false on failure
     */
    public function uploadProfilePicture($file) {
        $targetDir = __DIR__ . "/../../public/assets/img/profile-pictures/";
        
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        
        // Validate file type
        if (!in_array($fileExt, $allowedTypes)) {
            return false;
        }
        
        // Generate unique filename
        $fileName = uniqid('user_') . '.' . $fileExt;
        $targetFile = $targetDir . $fileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return 'assets/img/profile-pictures/' . $fileName;
        }
        
        return false;
    }

    /**
     * Update user profile picture
     * 
     * @param int $userId User ID
     * @param string $profilePicUrl Profile picture URL
     * @return bool Success status
     */
    public function updateProfilePicture($userId, $profilePicUrl) {
        $stmt = $this->conn->prepare("UPDATE users SET profile_pic_url = ? WHERE user_id = ?");
        $stmt->bind_param("si", $profilePicUrl, $userId);
        return $stmt->execute();
    }
}
