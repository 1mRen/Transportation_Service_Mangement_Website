<?php
// src/controllers/NotificationController.php

namespace Controllers;

class NotificationController {
    private $conn;

    public function __construct() {
        $this->conn = require __DIR__ . '/../config/database.php';
    }

    /**
     * Create a new notification
     */
    public function create($userId, $title, $message, $type = 'info') {
        $stmt = $this->conn->prepare("
            INSERT INTO notifications (user_id, title, message, type)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->bind_param("isss", $userId, $title, $message, $type);
        return $stmt->execute();
    }

    /**
     * Get notifications for a user
     * @param int $userId The user ID
     * @param int|null $limit Optional limit for number of notifications to return
     * @return array Array of notifications
     */
    public function getNotifications($userId, $limit = null) {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if ($limit !== null) {
            $stmt->bind_param("ii", $userId, $limit);
        } else {
            $stmt->bind_param("i", $userId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        
        return $notifications;
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount($userId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId) {
        $stmt = $this->conn->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE notification_id = ?
        ");
        
        $stmt->bind_param("i", $notificationId);
        return $stmt->execute();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId) {
        $stmt = $this->conn->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = ?
        ");
        
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    /**
     * Delete a notification
     */
    public function delete($notificationId) {
        $stmt = $this->conn->prepare("
            DELETE FROM notifications 
            WHERE notification_id = ?
        ");
        
        $stmt->bind_param("i", $notificationId);
        return $stmt->execute();
    }

    /**
     * Notify all admins
     */
    public function notifyAdmins($title, $message, $type = 'info') {
        // Get all admin users
        $stmt = $this->conn->prepare("
            SELECT user_id 
            FROM users 
            WHERE role = 'Admin'
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Create notification for each admin
        while ($row = $result->fetch_assoc()) {
            $this->create($row['user_id'], $title, $message, $type);
        }
    }
} 