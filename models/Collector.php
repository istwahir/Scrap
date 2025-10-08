<?php
require_once __DIR__ . '/../config.php';

class Collector {
    private $db;
    private $table = 'collectors';

    public function __construct() {
        $this->db = getDBConnection();
    }

    /**
     * Find collector by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name, u.phone
            FROM {$this->table} c
            JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find collector by user ID
     */
    public function findByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name, u.phone
            FROM {$this->table} c
            JOIN users u ON c.user_id = u.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new collector
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (user_id, license_file, vehicle_type, vehicle_number, status, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $data['user_id'],
            $data['license_file'] ?? null,
            $data['vehicle_type'] ?? null,
            $data['vehicle_number'] ?? null,
            $data['status'] ?? 'pending'
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Update collector
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
     * Update collector location
     */
    public function updateLocation($id, $lat, $lng) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET lat = ?, lng = ?, location_updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$lat, $lng, $id]);
    }

    /**
     * Get active collectors
     */
    public function getActiveCollectors() {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name, u.phone
            FROM {$this->table} c
            JOIN users u ON c.user_id = u.id
            WHERE c.status = 'approved'
            ORDER BY c.location_updated_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get pending collectors for admin approval
     */
    public function getPendingCollectors() {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name, u.phone
            FROM {$this->table} c
            JOIN users u ON c.user_id = u.id
            WHERE c.status = 'pending'
            ORDER BY c.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get collector statistics
     */
    public function getStats($collectorId) {
        // Get total collections
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_collections
            FROM collection_requests
            WHERE collector_id = ? AND status = 'completed'
        ");
        $stmt->execute([$collectorId]);
        $collections = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get active requests
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as active_requests
            FROM collection_requests
            WHERE collector_id = ? AND status IN ('assigned', 'en_route')
        ");
        $stmt->execute([$collectorId]);
        $active = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get today's earnings (estimated)
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(estimated_weight * 10), 0) as today_earnings
            FROM collection_requests
            WHERE collector_id = ?
            AND DATE(completed_at) = CURDATE()
            AND status = 'completed'
        ");
        $stmt->execute([$collectorId]);
        $earnings = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_collections' => (int)$collections['total_collections'],
            'active_requests' => (int)$active['active_requests'],
            'today_earnings' => (float)$earnings['today_earnings']
        ];
    }
}