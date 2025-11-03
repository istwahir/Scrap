<?php
/**
 * Smart Collector Assignment System
 * 
 * Automatically assigns the best collector to a request based on:
 * - Geographic proximity (service areas)
 * - Material matching (materials collected)
 * - Current workload (active requests)
 * - Availability status (online/active collectors)
 */

class CollectorAssignment {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Find the best collector for a given request
     * 
     * @param array $requestData Array containing: materials, latitude, longitude, pickup_address
     * @return int|null Collector ID or null if no suitable collector found
     */
    public function findBestCollector($requestData) {
        $materials = isset($requestData['materials']) ? $requestData['materials'] : '';
        $latitude = isset($requestData['latitude']) ? floatval($requestData['latitude']) : null;
        $longitude = isset($requestData['longitude']) ? floatval($requestData['longitude']) : null;
        $address = isset($requestData['pickup_address']) ? $requestData['pickup_address'] : '';
        
        // Get all active/approved collectors
        $stmt = $this->pdo->prepare("
            SELECT 
                c.id,
                c.name,
                c.service_areas,
                c.materials_collected,
                c.status,
                c.verified,
                cl.latitude as last_lat,
                cl.longitude as last_lng,
                cl.timestamp as last_location_time,
                COUNT(DISTINCT cr.id) as active_requests
            FROM collectors c
            LEFT JOIN (
                SELECT cl1.collector_id, cl1.latitude, cl1.longitude, cl1.timestamp
                FROM collector_locations cl1
                INNER JOIN (
                    SELECT collector_id, MAX(timestamp) as max_time
                    FROM collector_locations
                    GROUP BY collector_id
                ) cl2 ON cl1.collector_id = cl2.collector_id AND cl1.timestamp = cl2.max_time
            ) cl ON cl.collector_id = c.id
            LEFT JOIN collection_requests cr ON cr.collector_id = c.id 
                AND cr.status IN ('pending', 'assigned', 'en_route')
            WHERE c.status = 'approved'
              AND c.verified = 1
            GROUP BY c.id
        ");
        $stmt->execute();
        $collectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($collectors)) {
            error_log("No approved collectors available for assignment");
            return null;
        }
        
        // Score each collector
        $scoredCollectors = [];
        foreach ($collectors as $collector) {
            $score = $this->calculateCollectorScore($collector, $requestData);
            if ($score > 0) { // Only consider collectors with positive scores
                $scoredCollectors[] = [
                    'collector_id' => $collector['id'],
                    'name' => $collector['name'],
                    'score' => $score,
                    'active_requests' => $collector['active_requests']
                ];
            }
        }
        
        if (empty($scoredCollectors)) {
            error_log("No suitable collectors found after scoring");
            return null;
        }
        
        // Sort by score (highest first)
        usort($scoredCollectors, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        $bestCollector = $scoredCollectors[0];
        error_log(sprintf(
            "Assigned collector: %s (ID: %d) with score: %.2f, active requests: %d",
            $bestCollector['name'],
            $bestCollector['collector_id'],
            $bestCollector['score'],
            $bestCollector['active_requests']
        ));
        
        return $bestCollector['collector_id'];
    }
    
    /**
     * Calculate score for a collector based on multiple factors
     * Higher score = better match
     * 
     * @param array $collector Collector data
     * @param array $requestData Request data
     * @return float Score (0-100+)
     */
    private function calculateCollectorScore($collector, $requestData) {
        $score = 0;
        
        // 1. Material Matching (0-40 points)
        $materialScore = $this->scoreMaterialMatch(
            $collector['materials_collected'],
            $requestData['materials']
        );
        $score += $materialScore;
        
        // 2. Geographic Proximity (0-30 points)
        $locationScore = $this->scoreLocationMatch(
            $collector['service_areas'],
            $requestData['pickup_address'],
            $collector['last_lat'],
            $collector['last_lng'],
            $requestData['latitude'],
            $requestData['longitude']
        );
        $score += $locationScore;
        
        // 3. Workload (0-20 points) - Fewer active requests = higher score
        $workloadScore = $this->scoreWorkload($collector['active_requests']);
        $score += $workloadScore;
        
        // 4. Availability (0-10 points)
        $availabilityScore = $this->scoreAvailability(
            $collector['status'],
            $collector['last_location_time']
        );
        $score += $availabilityScore;
        
        return $score;
    }
    
    /**
     * Score material matching
     * @return float 0-40 points
     */
    private function scoreMaterialMatch($collectorMaterials, $requestMaterials) {
        if (empty($collectorMaterials) || empty($requestMaterials)) {
            return 20; // Neutral score if no materials specified
        }
        
        // Parse JSON materials
        $collectorMaterialsArray = json_decode($collectorMaterials, true);
        if (!is_array($collectorMaterialsArray)) {
            $collectorMaterialsArray = explode(',', $collectorMaterials);
        }
        $collectorMaterialsArray = array_map('trim', $collectorMaterialsArray);
        $collectorMaterialsArray = array_map('strtolower', $collectorMaterialsArray);
        
        // Parse request materials (can be comma-separated string or array)
        if (is_array($requestMaterials)) {
            $requestMaterialsArray = $requestMaterials;
        } else {
            $requestMaterialsArray = explode(',', $requestMaterials);
        }
        $requestMaterialsArray = array_map('trim', $requestMaterialsArray);
        $requestMaterialsArray = array_map('strtolower', $requestMaterialsArray);
        
        // Calculate match percentage
        $matchCount = count(array_intersect($requestMaterialsArray, $collectorMaterialsArray));
        $totalRequestMaterials = count($requestMaterialsArray);
        
        if ($totalRequestMaterials > 0) {
            $matchPercentage = $matchCount / $totalRequestMaterials;
            return $matchPercentage * 40; // Max 40 points for perfect match
        }
        
        return 20; // Neutral score
    }
    
    /**
     * Score location/service area matching
     * @return float 0-30 points
     */
    private function scoreLocationMatch($serviceAreas, $address, $collectorLat, $collectorLng, $requestLat, $requestLng) {
        $score = 0;
        
        // 1. Service Area Match (0-20 points)
        if (!empty($serviceAreas)) {
            $serviceAreasArray = json_decode($serviceAreas, true);
            if (!is_array($serviceAreasArray)) {
                $serviceAreasArray = explode(',', $serviceAreas);
            }
            $serviceAreasArray = array_map('trim', $serviceAreasArray);
            $serviceAreasArray = array_map('strtolower', $serviceAreasArray);
            
            // Check if address contains any service area
            $addressLower = strtolower($address);
            foreach ($serviceAreasArray as $area) {
                if (stripos($addressLower, $area) !== false) {
                    $score += 20; // Perfect service area match
                    break;
                }
            }
        }
        
        // 2. Geographic Distance (0-10 points)
        if ($collectorLat && $collectorLng && $requestLat && $requestLng) {
            $distance = $this->calculateDistance(
                $collectorLat, $collectorLng,
                $requestLat, $requestLng
            );
            
            // Score based on distance (closer = better)
            if ($distance < 2) { // Within 2km
                $score += 10;
            } elseif ($distance < 5) { // Within 5km
                $score += 7;
            } elseif ($distance < 10) { // Within 10km
                $score += 4;
            } elseif ($distance < 20) { // Within 20km
                $score += 2;
            }
            // No points if > 20km
        }
        
        return $score;
    }
    
    /**
     * Score based on current workload
     * @return float 0-20 points
     */
    private function scoreWorkload($activeRequests) {
        // Fewer active requests = higher score
        if ($activeRequests == 0) {
            return 20; // No active requests - best case
        } elseif ($activeRequests == 1) {
            return 15;
        } elseif ($activeRequests == 2) {
            return 10;
        } elseif ($activeRequests == 3) {
            return 5;
        } elseif ($activeRequests <= 5) {
            return 2;
        }
        // 0 points if more than 5 active requests
        return 0;
    }
    
    /**
     * Score based on availability status
     * @return float 0-10 points
     */
    private function scoreAvailability($status, $lastLocationTime) {
        $score = 0;
        
        // Status check - approved collectors only (already filtered in query)
        $score += 5;
        
        // Recent activity check
        if ($lastLocationTime) {
            $timeSinceLastUpdate = time() - strtotime($lastLocationTime);
            $minutesSinceUpdate = $timeSinceLastUpdate / 60;
            
            if ($minutesSinceUpdate < 5) {
                $score += 5; // Very active (last seen < 5 min)
            } elseif ($minutesSinceUpdate < 15) {
                $score += 3; // Recently active (last seen < 15 min)
            } elseif ($minutesSinceUpdate < 60) {
                $score += 1; // Active within last hour
            }
            // No points if last seen > 1 hour ago
        }
        
        return $score;
    }
    
    /**
     * Calculate distance between two coordinates using Haversine formula
     * @return float Distance in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Earth's radius in km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        return $distance;
    }
}
