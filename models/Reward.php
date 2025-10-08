<?php
require_once __DIR__ . '/../config.php';

class Reward {
    private $db;
    private $table = 'rewards';

    public function __construct() {
        $this->db = getDBConnection();
    }

    /**
     * Find reward by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get rewards by user ID
     */
    public function getByUserId($userId, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC";

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
     * Create new reward entry
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (user_id, points, description, type, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $data['user_id'],
            $data['points'],
            $data['description'] ?? null,
            $data['type'] ?? 'earned'
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Award points for completed collection
     */
    public function awardForCollection($userId, $requestId, $weight) {
        // Calculate points based on weight (10 points per kg)
        $points = ceil($weight * 10);

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (user_id, points, description, type, reference_id, created_at)
             VALUES (?, ?, ?, 'collection', ?, NOW())"
        );

        $description = "Points awarded for recycling {$weight}kg of materials";
        $stmt->execute([$userId, $points, $description, $requestId]);

        return $points;
    }

    /**
     * Redeem points
     */
    public function redeem($userId, $points, $description = null) {
        // Check if user has enough points
        $totalPoints = $this->getTotalPoints($userId);

        if ($totalPoints < $points) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (user_id, points, description, type, redeemed, created_at)
             VALUES (?, ?, ?, 'redemption', 1, NOW())"
        );

        $stmt->execute([$userId, -$points, $description ?? 'Points redeemed']);

        return true;
    }

    /**
     * Get total points for user
     */
    public function getTotalPoints($userId) {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(points), 0) as total_points
             FROM {$this->table}
             WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total_points'];
    }

    /**
     * Get available (unredeemed) points for user
     */
    public function getAvailablePoints($userId) {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(points), 0) as available_points
             FROM {$this->table}
             WHERE user_id = ? AND redeemed = 0"
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['available_points'];
    }

    /**
     * Get reward statistics for user
     */
    public function getStats($userId) {
        // Total earned
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(points), 0) as total_earned
             FROM {$this->table}
             WHERE user_id = ? AND points > 0"
        );
        $stmt->execute([$userId]);
        $earned = $stmt->fetch(PDO::FETCH_ASSOC);

        // Total redeemed
        $stmt = $this->db->prepare(
            "SELECT COALESCE(ABS(SUM(points)), 0) as total_redeemed
             FROM {$this->table}
             WHERE user_id = ? AND points < 0"
        );
        $stmt->execute([$userId]);
        $redeemed = $stmt->fetch(PDO::FETCH_ASSOC);

        // Available points
        $available = $this->getAvailablePoints($userId);

        // Recent transactions
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE user_id = ?
             ORDER BY created_at DESC LIMIT 10"
        );
        $stmt->execute([$userId]);
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_earned' => (int)$earned['total_earned'],
            'total_redeemed' => (int)$redeemed['total_redeemed'],
            'available_points' => $available,
            'recent_transactions' => $recent
        ];
    }

    /**
     * Get redemption options
     */
    public function getRedemptionOptions() {
        return [
            [
                'id' => 'mpesa_50',
                'name' => 'M-Pesa Airtime - KSh 50',
                'points_required' => 500,
                'type' => 'airtime',
                'value' => 50
            ],
            [
                'id' => 'mpesa_100',
                'name' => 'M-Pesa Airtime - KSh 100',
                'points_required' => 950,
                'type' => 'airtime',
                'value' => 100
            ],
            [
                'id' => 'mpesa_200',
                'name' => 'M-Pesa Airtime - KSh 200',
                'points_required' => 1800,
                'type' => 'airtime',
                'value' => 200
            ],
            [
                'id' => 'cash_100',
                'name' => 'M-Pesa Cash - KSh 100',
                'points_required' => 1000,
                'type' => 'cash',
                'value' => 100
            ],
            [
                'id' => 'cash_200',
                'name' => 'M-Pesa Cash - KSh 200',
                'points_required' => 1900,
                'type' => 'cash',
                'value' => 200
            ]
        ];
    }

    /**
     * Check if user can redeem specific option
     */
    public function canRedeem($userId, $optionId) {
        $options = $this->getRedemptionOptions();
        $availablePoints = $this->getAvailablePoints($userId);

        foreach ($options as $option) {
            if ($option['id'] === $optionId) {
                return $availablePoints >= $option['points_required'];
            }
        }

        return false;
    }

    /**
     * Process redemption
     */
    public function processRedemption($userId, $optionId) {
        $options = $this->getRedemptionOptions();

        foreach ($options as $option) {
            if ($option['id'] === $optionId) {
                if ($this->canRedeem($userId, $optionId)) {
                    $description = "Redeemed for: {$option['name']}";
                    return $this->redeem($userId, $option['points_required'], $description);
                }
                break;
            }
        }

        return false;
    }
}