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
            UPDATE users SET name = ?, email = ?, username = ?, role = ?, applicant_id = ?, updated_at = NOW()
            WHERE user_id = ?
        ");

        $stmt->bind_param(
            "ssssii",
            $data['name'],
            $data['email'],
            $data['username'],
            $data['role'],
            $data['applicant_id'],
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
}
