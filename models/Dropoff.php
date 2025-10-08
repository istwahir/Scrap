<?php
require_once __DIR__ . '/../config.php';

class Dropoff {
    private $db;
    private $table = 'dropoff_points';

    public function __construct() {
        $this->db = getDBConnection();
    }

    /**
     * Find dropoff point by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all active dropoff points
     */
    public function getAllActive() {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find nearby dropoff points
     */
    public function findNearby($lat, $lng, $radiusKm = 5, $materials = null) {
        $sql = "
            SELECT *,
            (
                6371 * acos(
                    cos(radians(?)) *
                    cos(radians(lat)) *
                    cos(radians(lng) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(lat))
                )
            ) as distance
            FROM {$this->table}
            WHERE status = 'active'
            HAVING distance <= ?
        ";

        $params = [$lat, $lng, $lat, $radiusKm];

        if ($materials) {
            $materialArray = explode(',', $materials);
            $conditions = [];
            foreach ($materialArray as $material) {
                $conditions[] = "FIND_IN_SET(?, materials)";
                $params[] = $material;
            }
            $sql .= " AND (" . implode(' OR ', $conditions) . ")";
        }

        $sql .= " ORDER BY distance ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new dropoff point
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (
                name, address, lat, lng, materials, operating_hours,
                contact_phone, contact_email, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $data['name'],
            $data['address'],
            $data['lat'],
            $data['lng'],
            $data['materials'],
            $data['operating_hours'] ?? '8AM - 6PM',
            $data['contact_phone'] ?? null,
            $data['contact_email'] ?? null,
            $data['status'] ?? 'active'
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Update dropoff point
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
     * Get dropoff points by material type
     */
    public function getByMaterial($material) {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE status = 'active' AND FIND_IN_SET(?, materials)
             ORDER BY name ASC"
        );
        $stmt->execute([$material]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get available materials across all dropoff points
     */
    public function getAvailableMaterials() {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT materials FROM {$this->table} WHERE status = 'active'"
        );
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $materials = [];
        foreach ($results as $result) {
            $materialList = explode(',', $result['materials']);
            $materials = array_merge($materials, $materialList);
        }

        return array_unique(array_filter($materials));
    }

    /**
     * Calculate distance between two points (Haversine formula)
     */
    public static function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * asin(sqrt($a));

        return $earthRadius * $c;
    }

    /**
     * Get dropoff point statistics
     */
    public function getStats() {
        // Total active dropoff points
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total_active FROM {$this->table} WHERE status = 'active'"
        );
        $stmt->execute();
        $active = $stmt->fetch(PDO::FETCH_ASSOC);

        // Material coverage
        $materials = $this->getAvailableMaterials();

        // Requests per dropoff point
        $stmt = $this->db->prepare("
            SELECT d.name, COUNT(r.id) as request_count
            FROM {$this->table} d
            LEFT JOIN collection_requests r ON d.id = r.dropoff_point_id
            WHERE d.status = 'active'
            GROUP BY d.id, d.name
            ORDER BY request_count DESC
            LIMIT 10
        ");
        $stmt->execute();
        $popular = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_active' => (int)$active['total_active'],
            'available_materials' => $materials,
            'material_count' => count($materials),
            'most_popular' => $popular
        ];
    }
}