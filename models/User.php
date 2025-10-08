<?php
require_once __DIR__ . '/../config.php';

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = getDBConnection();
    }

    /**
     * Find user by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find user by phone number
     */
    public function findByPhone($phone) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE phone = ?");
        $stmt->execute([$phone]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new user
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (name, phone, otp, otp_expires, role, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $data['name'] ?? null,
            $data['phone'],
            $data['otp'] ?? null,
            $data['otp_expires'] ?? null,
            $data['role'] ?? 'user'
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?"
        );

        return $stmt->execute($values);
    }

    /**
     * Update OTP for user
     */
    public function updateOTP($phone, $otp, $expires) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET otp = ?, otp_expires = ? WHERE phone = ?"
        );
        return $stmt->execute([$otp, $expires, $phone]);
    }

    /**
     * Clear OTP for user
     */
    public function clearOTP($id) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET otp = NULL, otp_expires = NULL WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Get user statistics
     */
    public function getStats($userId) {
        // Get total points
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(points), 0) as total_points
            FROM rewards
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $points = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get total requests
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_requests
            FROM collection_requests
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $requests = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get completed requests
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as completed_requests
            FROM collection_requests
            WHERE user_id = ? AND status = 'completed'
        ");
        $stmt->execute([$userId]);
        $completed = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_points' => (int)$points['total_points'],
            'total_requests' => (int)$requests['total_requests'],
            'completed_requests' => (int)$completed['completed_requests']
        ];
    }
}