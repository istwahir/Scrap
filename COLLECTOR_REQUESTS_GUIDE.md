# Collector Request Display & Management System

## Overview
Collectors can now see and manage requests that have been automatically assigned to them through the Smart Auto-Assignment system.

## How Requests Are Displayed

### Dashboard View (`/public/collectors/dashboard.php`)

Collectors see requests in **three categories**:

#### 1. **Pending Requests** (New - Awaiting Action)
- **Status**: `pending`
- **Description**: Requests that have been auto-assigned to the collector but not yet accepted
- **Actions Available**:
  - ✅ **Accept** - Collector accepts the job, status changes to `assigned`
  - ❌ **Decline** - Collector declines, request is reassigned to another collector
- **Display Location**: "All Requests" section with filter

#### 2. **Active Requests** (In Progress)
- **Status**: `assigned` or `en_route`
- **Description**: Requests the collector has accepted and is currently working on
- **Actions Available**:
  - ✅ **Complete** - Mark collection as completed
  - 📍 **Locate** - Show on map
- **Display Location**: "Active Requests" panel (top of dashboard)

#### 3. **History** (Completed)
- **Status**: `completed`
- **Description**: Past collections completed by this collector
- **Display Location**: "Collection History" section

## Request Workflow

### Complete Flow from Creation to Completion

```
┌─────────────────────────────────────────────────────┐
│ 1. CITIZEN CREATES REQUEST                          │
│    - Selects materials, location, date              │
│    - Uploads photo                                   │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 2. SMART AUTO-ASSIGNMENT                            │
│    - System finds best collector                    │
│    - Based on: proximity, materials, workload       │
│    - Assigns collector_id                           │
│    - Status: 'pending'                              │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 3. COLLECTOR SEES REQUEST                           │
│    - Appears in "Pending Requests"                  │
│    - Shows: customer, materials, address, time      │
│    - Can Accept or Decline                          │
└────────────────┬────────────────────────────────────┘
                 │
         ┌───────┴───────┐
         │               │
         ▼               ▼
    [ACCEPT]        [DECLINE]
         │               │
         │               ▼
         │    ┌─────────────────────────┐
         │    │ Request Reassigned      │
         │    │ - collector_id = NULL   │
         │    │ - Auto-assign to next   │
         │    │   best collector        │
         │    └─────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────┐
│ 4. REQUEST ACCEPTED                                 │
│    - Status: 'assigned'                             │
│    - Moves to "Active Requests"                     │
│    - Removed from "Pending Requests"                │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 5. COLLECTION IN PROGRESS                           │
│    - Collector navigates to location                │
│    - Can mark as "En Route" (optional)              │
│    - Status: 'en_route'                             │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│ 6. COLLECTION COMPLETED                             │
│    - Collector clicks "Complete"                    │
│    - Status: 'completed'                            │
│    - Moves to "History"                             │
│    - Citizen earns points                           │
└─────────────────────────────────────────────────────┘
```

## API Endpoints

### For Collectors

#### Get Dashboard Data
```
GET /api/collectors/dashboard.php

Response:
{
  "status": "success",
  "stats": { ... },
  "activeRequests": [ ... ],      // Status: assigned, en_route
  "pendingRequests": [ ... ],     // Status: pending
  "history": [ ... ],              // Status: completed
  "vehicle": { ... },
  "areas": [ ... ]
}
```

#### Accept Request
```
POST /api/collectors/accept_request.php
{
  "request_id": 42
}

Response:
{
  "status": "success",
  "message": "Request accepted successfully",
  "request": {
    "id": 42,
    "customer_name": "John Doe",
    "address": "Kiambu Town",
    "material_type": "plastic, paper"
  }
}
```

#### Decline Request
```
POST /api/collectors/decline_request.php
{
  "request_id": 42
}

Response:
{
  "status": "success",
  "message": "Request declined successfully"
}

Note: Request is automatically reassigned to another collector
```

#### Complete Collection
```
POST /api/collectors/complete_collection.php
{
  "request_id": 42,
  "actual_weight": 25.5,
  "notes": "Collection completed successfully"
}

Response:
{
  "status": "success",
  "message": "Collection completed successfully"
}
```

## UI Components

### Dashboard Sections

#### 1. Statistics Cards (Top Row)
- Today's Collections
- Today's Earnings
- Total Weight Today
- Rating

#### 2. Active Requests Panel
```html
Shows currently accepted requests with:
- Customer name
- Material type
- Address
- Actions: [Complete] [Locate]
```

#### 3. All Requests Section (with filter)
```html
Filter: [All] [Pending] [Accepted]

For each pending request:
- Customer name
- Material type
- Address
- Requested time
- Actions: [Accept] [Decline]
```

#### 4. Collection History
```html
Shows completed collections:
- Customer name
- Material type and weight
- Address
- Completion date
```

## JavaScript Functions

### Key Functions in `collector-dashboard.js`

```javascript
// Load all dashboard data
async function loadDashboardData()

// Render requests in all sections
function renderActiveRequests(requests)
function renderRequestsList(pendingRequests)
function renderHistory(history)

// Action handlers
async function acceptRequest(id)
async function declineRequest(id)
async function completeCollection(id)
```

### Event Listeners

```javascript
// Click handlers for action buttons
document.body.addEventListener('click', (e) => {
  const t = e.target;
  if (t.matches('[data-accept]')) acceptRequest(t.getAttribute('data-accept'));
  if (t.matches('[data-decline]')) declineRequest(t.getAttribute('data-decline'));
  if (t.matches('[data-complete]')) completeCollection(t.getAttribute('data-complete'));
});
```

## Testing the System

### Test Scenario 1: Create and Accept Request

1. **As Citizen**:
   ```
   - Login as citizen user
   - Create new collection request
   - Note the request ID from response
   - Check that collector_id is assigned
   ```

2. **As Collector**:
   ```
   - Login as the assigned collector
   - Open dashboard
   - Verify request appears in "Pending Requests"
   - Click "Accept"
   - Verify request moves to "Active Requests"
   ```

3. **Complete Collection**:
   ```
   - Click "Complete" on active request
   - Verify request moves to "History"
   ```

### Test Scenario 2: Decline and Reassignment

1. **As Collector**:
   ```
   - See pending request
   - Click "Decline"
   - Request disappears from your dashboard
   ```

2. **System Behavior**:
   ```
   - Request collector_id set to NULL
   - Auto-assignment runs
   - New best collector found
   - Request appears in new collector's dashboard
   ```

3. **Verify in Database**:
   ```sql
   SELECT id, collector_id, status, created_at, updated_at
   FROM collection_requests
   WHERE id = <request_id>;
   ```

## Common Issues & Solutions

### Issue 1: Requests Not Showing

**Problem**: Collector logs in but sees no pending requests

**Possible Causes**:
1. No requests assigned to this collector
2. Collector not approved
3. Database query issues

**Solution**:
```sql
-- Check if collector has pending requests
SELECT * FROM collection_requests 
WHERE collector_id = <collector_id> 
AND status = 'pending';

-- Check collector status
SELECT * FROM collectors WHERE id = <collector_id>;
```

### Issue 2: Accept Button Not Working

**Problem**: Clicking "Accept" doesn't work

**Check**:
1. Browser console for JavaScript errors
2. Network tab for API response
3. PHP error logs for server errors

**Common Fix**:
```javascript
// Verify JavaScript is loaded
console.log('Dashboard JS loaded');

// Check if button has correct data attribute
<button data-accept="42">Accept</button>
```

### Issue 3: Request Already Processed Error

**Problem**: "Request not found or already processed"

**Cause**: Another collector already accepted the request, or it was manually changed

**Solution**: Reload dashboard to see current state

## Database Schema Reference

### collection_requests Table
```sql
- id: Primary key
- user_id: Citizen who created request
- collector_id: Assigned collector (NULL if unassigned)
- materials: Comma-separated material types
- status: pending, assigned, en_route, completed, cancelled
- pickup_address: Collection location
- pickup_date: Scheduled date
- pickup_time: Scheduled time
- estimated_weight: Weight estimate
- photo_url: Photo of materials
- created_at: Request creation time
- updated_at: Last update time
```

### Key Status Values
- **pending**: Assigned but not accepted by collector
- **assigned**: Accepted by collector
- **en_route**: Collector on the way
- **completed**: Collection finished
- **cancelled**: Request cancelled

## Performance Considerations

### Optimizations
1. **Caching**: Dashboard data cached for 30 seconds
2. **Pagination**: History limited to 20 recent items
3. **Lazy Loading**: Map markers loaded on demand
4. **Debouncing**: Filter changes debounced by 300ms

### Database Indexes
```sql
-- Recommended indexes for performance
CREATE INDEX idx_collector_status ON collection_requests(collector_id, status);
CREATE INDEX idx_created_at ON collection_requests(created_at);
CREATE INDEX idx_status ON collection_requests(status);
```

## Future Enhancements

Potential improvements:
- [ ] Real-time notifications (WebSocket/SSE)
- [ ] Push notifications for new requests
- [ ] Batch accept multiple requests
- [ ] Request priority/urgency levels
- [ ] Estimated arrival time calculator
- [ ] In-app messaging with citizens
- [ ] Photo upload at completion
- [ ] Digital signatures

## Support

For issues:
- Check browser console for errors
- Review PHP error logs
- Verify database schema matches expectations
- Test API endpoints directly with Postman
- Check AUTO_ASSIGNMENT_GUIDE.md for assignment logic
