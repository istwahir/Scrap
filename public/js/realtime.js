// Check if EventSource is supported
if (typeof EventSource !== 'undefined') {
    const eventSource = new EventSource('/api/events.php');
    
    // Handle updates
    eventSource.addEventListener('update', function(event) {
        const data = JSON.parse(event.data);
        
        switch (data.type) {
            case 'request_update':
                // Update request status in UI
                updateRequestStatus(data.request_id, data.status);
                // Update collector location if available
                if (data.collector_location) {
                    updateCollectorLocation(
                        data.request_id,
                        data.collector_location.lat,
                        data.collector_location.lng
                    );
                }
                break;
                
            default:
                console.log('Unknown update type:', data.type);
        }
    });
    
    // Handle notifications
    eventSource.addEventListener('notification', function(event) {
        const notification = JSON.parse(event.data);
        showNotification(notification);
    });
    
    // Handle connection errors
    eventSource.onerror = function(error) {
        console.error('SSE Error:', error);
        eventSource.close();
        // Try to reconnect after 5 seconds
        setTimeout(() => {
            window.location.reload();
        }, 5000);
    };
} else {
    console.log('Server-Sent Events not supported. Falling back to polling.');
    // Implement polling fallback
    setInterval(checkUpdates, 10000);
}

// Update request status in UI
function updateRequestStatus(requestId, status) {
    const statusElement = document.querySelector(`[data-request-id="${requestId}"] .status`);
    if (statusElement) {
        statusElement.textContent = status;
        statusElement.className = `status ${getStatusColor(status)}`;
    }
    
    // Refresh requests list if on dashboard
    if (window.location.pathname === '/dashboard.html') {
        loadRecentRequests();
    }
}

// Update collector location on map
function updateCollectorLocation(requestId, lat, lng) {
    if (typeof map !== 'undefined' && collectorMarkers[requestId]) {
        collectorMarkers[requestId].setLatLng([lat, lng]);
    }
}

// Show notification
function showNotification(notification) {
    // Update notification count
    const count = parseInt(document.getElementById('notificationCount').textContent || '0') + 1;
    document.getElementById('notificationCount').textContent = count;
    document.getElementById('notificationCount').classList.remove('hidden');
    
    // Add notification to list if panel is open
    const list = document.getElementById('notificationList');
    if (list) {
        const notificationHtml = `
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <p class="font-medium">${notification.title}</p>
                <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                <p class="text-xs text-gray-500 mt-2">
                    ${new Date(notification.created_at).toLocaleString()}
                </p>
            </div>
        `;
        list.insertAdjacentHTML('afterbegin', notificationHtml);
    }
    
    // Show browser notification if permitted
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(notification.title, {
            body: notification.message,
            icon: '/images/icon-192x192.png'
        });
    }
}

// Polling fallback
async function checkUpdates() {
    try {
        const response = await fetch('/api/updates.php');
        const data = await response.json();
        
        if (data.status === 'success') {
            // Handle request updates
            data.updates.forEach(update => {
                updateRequestStatus(update.request_id, update.status);
                if (update.collector_location) {
                    updateCollectorLocation(
                        update.request_id,
                        update.collector_location.lat,
                        update.collector_location.lng
                    );
                }
            });
            
            // Handle notifications
            data.notifications.forEach(showNotification);
        }
    } catch (error) {
        console.error('Update check failed:', error);
    }
}