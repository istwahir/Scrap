<?php
require_once __DIR__ . '/../config.php';

class Request {
    private $db;
    private $table = 'collection_requests';

    public function __construct() {
        $this->db = getDBConnection();
    }

    /**
     * Find request by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as user_name, u.phone as user_phone,
                   c.name as collector_name, c.phone as collector_phone,
                   d.name as dropoff_name
            FROM {$this->table} r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN collectors col ON r.collector_id = col.id
            LEFT JOIN users c ON col.user_id = c.id
            LEFT JOIN dropoff_points d ON r.dropoff_point_id = d.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get requests by user ID
     */
    public function getByUserId($userId, $limit = null, $offset = 0) {
        $sql = "
            SELECT r.*, c.name as collector_name,
                   d.name as dropoff_name, d.address as dropoff_address
            FROM {$this->table} r
            LEFT JOIN collectors col ON r.collector_id = col.id
            LEFT JOIN users c ON col.user_id = c.id
            LEFT JOIN dropoff_points d ON r.dropoff_point_id = d.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit) {
            $stmt->execute([$userId, $limit, $offset]);
        } else {
            $stmt->execute([$userId]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get requests by collector ID
     */
    public function getByCollectorId($collectorId, $status = null) {
        $sql = "
            SELECT r.*, u.name as user_name, u.phone as user_phone,
                   d.name as dropoff_name, d.address as dropoff_address
            FROM {$this->table} r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN dropoff_points d ON r.dropoff_point_id = d.id
            WHERE r.collector_id = ?
        ";

        $params = [$collectorId];

        if ($status) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new request
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (
                user_id, materials, estimated_weight, pickup_address,
                lat, lng, pickup_date, pickup_time, photo_url, notes,
                status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $data['user_id'],
            $data['materials'],
            $data['estimated_weight'] ?? null,
            $data['pickup_address'],
            $data['lat'],
            $data['lng'],
            $data['pickup_date'],
            $data['pickup_time'],
            $data['photo_url'] ?? null,
            $data['notes'] ?? null,
            $data['status'] ?? 'pending'
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Update request
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
     * Assign collector to request
     */
    public function assignCollector($requestId, $collectorId) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET collector_id = ?, status = 'assigned', assigned_at = NOW()
             WHERE id = ?"
        );
        return $stmt->execute([$collectorId, $requestId]);
    }

    /**
     * Update request status
     */
    public function updateStatus($id, $status, $additionalData = []) {
        $fields = ["status = ?"];
        $values = [$status];

        // Add status-specific timestamps
        switch ($status) {
            case 'en_route':
                $fields[] = "en_route_at = NOW()";
                break;
            case 'completed':
                $fields[] = "completed_at = NOW()";
                break;
            case 'cancelled':
                $fields[] = "cancelled_at = NOW()";
                break;
        }

        // Add any additional data
        foreach ($additionalData as $key => $value) {
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
     * Get pending requests for assignment
     */
    public function getPendingRequests() {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as user_name, u.phone as user_phone
            FROM {$this->table} r
            JOIN users u ON r.user_id = u.id
            WHERE r.status = 'pending'
            ORDER BY r.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get requests by status
     */
    public function getByStatus($status, $limit = null) {
        $sql = "
            SELECT r.*, u.name as user_name, u.phone as user_phone,
                   c.name as collector_name
            FROM {$this->table} r
            JOIN users u ON r.user_id = u.id
            LEFT JOIN collectors col ON r.collector_id = col.id
            LEFT JOIN users c ON col.user_id = c.id
            WHERE r.status = ?
            ORDER BY r.created_at DESC
        ";

        if ($limit) {
            $sql .= " LIMIT ?";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit) {
            $stmt->execute([$status, $limit]);
        } else {
            $stmt->execute([$status]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate environmental impact for completed requests
     */
    public function calculateEnvironmentalImpact($userId) {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_collections,
                COALESCE(SUM(estimated_weight), 0) as total_weight_kg,
                COALESCE(SUM(estimated_weight * 50), 0) as co2_reduced_grams,
                COALESCE(SUM(estimated_weight * 0.5), 0) as trees_saved,
                COALESCE(SUM(estimated_weight * 100), 0) as water_saved_liters
            FROM {$this->table}
            WHERE user_id = ? AND status = 'completed'
        ");
        $stmt->execute([$userId]);
        $impact = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_collections' => (int)$impact['total_collections'],
            'total_weight_kg' => (float)$impact['total_weight_kg'],
            'co2_reduced_kg' => round($impact['co2_reduced_grams'] / 1000, 2),
            'trees_saved' => round($impact['trees_saved'], 1),
            'water_saved_liters' => (float)$impact['water_saved_liters']
        ];
    }
}