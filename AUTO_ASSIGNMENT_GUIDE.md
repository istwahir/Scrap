# Smart Collector Auto-Assignment System

## Overview

The Smart Collector Auto-Assignment system automatically assigns the best available collector to new collection requests based on multiple factors including geographic proximity, material matching, current workload, and availability status.

## How It Works

### Scoring Algorithm

Each collector is scored on a scale of 0-100+ based on four key factors:

#### 1. Material Matching (0-40 points)
- **Perfect Match (40 pts)**: Collector handles all requested materials
- **Partial Match (20-39 pts)**: Collector handles some requested materials
- **No Match (0-20 pts)**: Collector doesn't handle requested materials specifically

**Example:**
- Request: plastic, paper
- Collector handles: plastic, paper, metal → 40 points ✓
- Collector handles: plastic only → 20 points
- Collector handles: metal, glass only → 20 points (neutral)

#### 2. Geographic Proximity (0-30 points)

##### Service Area Match (0-20 points)
- **Exact Match (20 pts)**: Request address contains collector's service area
- Example: Request in "Kiambu Town" matches collector serving "Kiambu"

##### Distance-Based Scoring (0-10 points)
- **< 2km**: 10 points
- **2-5km**: 7 points
- **5-10km**: 4 points
- **10-20km**: 2 points
- **> 20km**: 0 points

#### 3. Workload (0-20 points)
Based on number of active requests (pending, assigned, en_route):
- **0 active requests**: 20 points (best)
- **1 active request**: 15 points
- **2 active requests**: 10 points
- **3 active requests**: 5 points
- **4-5 active requests**: 2 points
- **> 5 active requests**: 0 points

#### 4. Availability (0-10 points)

##### Approval Status (5 points)
- Collector must be approved and verified

##### Recent Activity (0-5 points)
Based on last location update:
- **< 5 minutes ago**: 5 points (very active)
- **5-15 minutes ago**: 3 points (recently active)
- **15-60 minutes ago**: 1 point (active)
- **> 1 hour ago**: 0 points (inactive)

### Total Score Calculation

```
Total Score = Material Match + Geographic Proximity + Workload + Availability
Maximum Possible Score = 40 + 30 + 20 + 10 = 100 points
```

The collector with the **highest score** is automatically assigned to the request.

## Implementation

### File Structure

```
includes/
  └── CollectorAssignment.php          # Main assignment logic
api/
  └── create_request.php                # Integrated auto-assignment
scripts/
  └── test_auto_assignment.php         # Test script
```

### Usage

#### Automatic Assignment (Default)
When a citizen creates a request without specifying a collector:

```php
POST /api/create_request.php
{
  "materials": ["plastic", "paper"],
  "address": "Kiambu Town",
  "lat": -1.1712,
  "lng": 36.8356,
  "date": "2025-11-10",
  "time": "morning"
}

Response:
{
  "status": "success",
  "message": "Request created and assigned to a collector",
  "request_id": 42,
  "collector_id": 7,
  "collector_assigned": true,
  "points_earned": 5
}
```

#### Manual Assignment (Override)
If a specific collector is provided, auto-assignment is skipped:

```php
POST /api/create_request.php
{
  "collector_id": 5,  // Explicitly specified
  "materials": ["plastic"],
  ...
}
```

#### Fallback Behavior
If no suitable collector is found:

```php
Response:
{
  "status": "success",
  "message": "Request created. A collector will be assigned soon.",
  "request_id": 42,
  "collector_id": null,
  "collector_assigned": false,
  "points_earned": 5
}
```

The request is created with `collector_id = NULL` and can be manually assigned later by an administrator.

## Testing

### Run Test Script

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/Scrap
php scripts/test_auto_assignment.php
```

The test script will:
1. Test assignment for different material types and locations
2. Display which collector was assigned and why
3. Show statistics for all approved collectors

### Manual Testing

1. **Create a test request** as a citizen
2. **Check the response** to see if a collector was assigned
3. **Log in as the assigned collector** and verify the request appears in their pending requests
4. **Check logs** in error_log for assignment details

### Example Test Scenarios

#### Scenario 1: High-Priority Collector (Perfect Match)
- Collector has no active requests
- Located 3km from request
- Handles exact materials requested
- Last seen 2 minutes ago
- **Expected Score**: ~87 points

#### Scenario 2: Busy Collector (Lower Priority)
- Collector has 4 active requests
- Located 15km from request
- Handles some materials
- Last seen 30 minutes ago
- **Expected Score**: ~32 points

#### Scenario 3: No Match (Not Assigned)
- Collector in wrong service area
- Doesn't handle requested materials
- Has too many active requests
- Inactive for > 1 hour
- **Expected Score**: ~5 points (likely not assigned)

## Configuration

### Eligibility Criteria

Only collectors meeting these criteria are considered:
- ✓ Status: `approved`
- ✓ Verified: `1` (verified)
- ✓ Has service areas defined
- ✓ Has materials specified

### Adjusting Weights

To modify the scoring weights, edit `includes/CollectorAssignment.php`:

```php
// Current weights
private function calculateCollectorScore($collector, $requestData) {
    $score = 0;
    
    $score += $this->scoreMaterialMatch(...);      // 0-40 points
    $score += $this->scoreLocationMatch(...);      // 0-30 points
    $score += $this->scoreWorkload(...);           // 0-20 points
    $score += $this->scoreAvailability(...);       // 0-10 points
    
    return $score;
}
```

### Distance Thresholds

Adjust in `scoreLocationMatch()` method:

```php
if ($distance < 2) {        // Within 2km
    $score += 10;
} elseif ($distance < 5) {  // Within 5km
    $score += 7;
} elseif ($distance < 10) { // Within 10km
    $score += 4;
}
```

### Workload Limits

Adjust in `scoreWorkload()` method:

```php
if ($activeRequests == 0) {
    return 20;  // No active requests
} elseif ($activeRequests <= 2) {
    return 15;  // Light workload
} elseif ($activeRequests <= 5) {
    return 5;   // Heavy workload
}
return 0;       // Overloaded
```

## Benefits

### For Citizens
- ✓ Instant assignment - no waiting
- ✓ Best match based on location and materials
- ✓ Higher chance of quick pickup

### For Collectors
- ✓ Fair distribution of requests
- ✓ Requests match their capabilities
- ✓ Located within service areas
- ✓ Balanced workload

### For Administrators
- ✓ Automated process reduces manual work
- ✓ Transparent scoring system
- ✓ Detailed logging for troubleshooting
- ✓ Fallback to manual assignment when needed

## Logging

The system logs assignment decisions:

```
Smart assignment: Assigned collector ID 7 to new request
- Material match: 40/40
- Location match: 27/30
- Workload: 20/20
- Availability: 8/10
- Total score: 95
```

Check logs at: `/Applications/XAMPP/xamppfiles/logs/error_log` or your PHP error log location.

## Troubleshooting

### No Collector Assigned

**Problem**: Request created but no collector assigned

**Possible Causes**:
1. No approved collectors in the system
2. No collectors serve the requested area
3. No collectors handle the requested materials
4. All collectors are overloaded (> 5 active requests)

**Solution**:
- Verify collectors exist: `SELECT * FROM collectors WHERE status='approved'`
- Check service areas match request location
- Verify materials_collected includes request materials
- Reduce active requests or add more collectors

### Wrong Collector Assigned

**Problem**: Collector assigned doesn't seem like the best match

**Check**:
1. Run test script to see scoring details
2. Review collector's service_areas and materials_collected
3. Check collector's current location vs request location
4. Verify collector's active request count

**Adjust**: Modify scoring weights if needed

### Performance Issues

**Problem**: Assignment takes too long

**Optimization**:
- Add indexes on frequently queried columns
- Cache collector data
- Reduce distance calculation complexity
- Limit collector query results

## Future Enhancements

Potential improvements:
- [ ] Time-based availability (working hours)
- [ ] Collector preferences and specializations
- [ ] Dynamic pricing based on distance
- [ ] Request batching for nearby pickups
- [ ] Machine learning for better predictions
- [ ] Real-time traffic consideration
- [ ] Collector reputation/rating factor

## Support

For issues or questions:
- Check error logs for detailed messages
- Run test script to verify system status
- Review collector data in database
- Contact system administrator
