// Collector tracking module
class CollectorTracker {
    constructor(map, options = {}) {
        this.map = map;
        this.markers = new Map();
        this.eventSource = null;
        this.options = {
            updateInterval: options.updateInterval || 3000,
            inactiveTimeout: options.inactiveTimeout || 300000, // 5 minutes
            onCollectorClick: options.onCollectorClick || null,
            onStatusChange: options.onStatusChange || null
        };

        // Initialize collector markers with custom icons
        this.icons = {
            truck: L.icon({
                iconUrl: '/Scrap/public/images/markers/truck.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            }),
            pickup: L.icon({
                iconUrl: '/Scrap/public/images/markers/pickup.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            }),
            tuktuk: L.icon({
                iconUrl: '/Scrap/public/images/markers/tuktuk.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            }),
            motorcycle: L.icon({
                iconUrl: '/Scrap/public/images/markers/motorcycle.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            })
        };
    }

    // Start tracking
    startTracking() {
        if (this.eventSource) {
            this.stopTracking();
        }

        // Try the expected base path first; if it fails (some pages may request without /Scrap), fallback to the alternative path
        try {
            this.eventSource = new EventSource('/Scrap/api/collectors/get_locations.php');
        } catch (e) {
            console.warn('Failed to open EventSource to /Scrap, trying /api path', e);
            try { this.eventSource = new EventSource('/api/collectors/get_locations.php'); } catch (e2) { console.error('Failed to open fallback EventSource', e2); }
        }

        this.eventSource.addEventListener('update', (event) => {
            const data = JSON.parse(event.data);
            if (data.status === 'success') {
                this.updateMarkers(data.collectors);
            }
        });

        // Enhanced error handling: if primary path fails, attempt a fallback without /Scrap once.
        this._esTriedFallback = false;
        this.eventSource.addEventListener('error', (event) => {
            console.error('Tracking error:', event);
            // If we haven't tried fallback yet and current URL looks like /Scrap, try fallback
            try {
                const url = this.eventSource.url || (this.eventSource && this.eventSource._url) || '';
                if (!this._esTriedFallback && url.indexOf('/Scrap/') !== -1) {
                    console.warn('Attempting fallback EventSource URL without /Scrap');
                    this._esTriedFallback = true;
                    try { this.eventSource.close(); } catch(e){}
                    try {
                        this.eventSource = new EventSource('/api/collectors/get_locations.php');
                        // Rebind handlers
                        this.eventSource.addEventListener('update', (ev) => {
                            const data = JSON.parse(ev.data);
                            if (data.status === 'success') this.updateMarkers(data.collectors);
                        });
                        // Let the same error handler manage further errors (it will not try fallback again)
                    } catch (e2) {
                        console.error('Fallback ES construction failed', e2);
                        this.stopTracking();
                        setTimeout(() => this.startTracking(), 5000);
                    }
                    return;
                }
            } catch (ex) { console.error('Error in ES fallback logic', ex); }

            // Default: stop tracking and attempt reconnection after a delay
            this.stopTracking();
            setTimeout(() => this.startTracking(), 5000);
        });

        // Start sending location if user is a collector
        if (this.isCollector()) {
            this.startSendingLocation();
        }
    }

    // Stop tracking
    stopTracking() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
        if (this.locationInterval) {
            clearInterval(this.locationInterval);
            this.locationInterval = null;
        }
        // Clear all markers
        this.markers.forEach(marker => this.map.removeLayer(marker));
        this.markers.clear();
    }

    // Update collector markers on the map
    updateMarkers(collectors) {
        const currentIds = new Set(collectors.map(c => c.id));
        
        // Remove stale markers
        this.markers.forEach((marker, id) => {
            if (!currentIds.has(id)) {
                this.map.removeLayer(marker);
                this.markers.delete(id);
            }
        });

        // Update or add markers
        collectors.forEach(collector => {
            const marker = this.markers.get(collector.id);
            const icon = this.icons[collector.vehicle];

            if (marker) {
                // Update existing marker
                marker.setLatLng([collector.position.lat, collector.position.lng]);
                marker.setPopupContent(this.createPopupContent(collector));
            } else {
                // Create new marker
                const newMarker = L.marker([collector.position.lat, collector.position.lng], {
                    icon: icon
                }).addTo(this.map);

                newMarker.bindPopup(this.createPopupContent(collector));
                
                if (this.options.onCollectorClick) {
                    newMarker.on('click', () => this.options.onCollectorClick(collector));
                }

                this.markers.set(collector.id, newMarker);
            }
        });

        // Trigger status change callback if provided
        if (this.options.onStatusChange) {
            this.options.onStatusChange(collectors);
        }
    }

    // Create popup content for collector marker
    createPopupContent(collector) {
        return `
            <div class="collector-popup">
                <h3 class="font-bold">${collector.name}</h3>
                <p class="text-sm">
                    <span class="font-medium">Status:</span> 
                    <span class="status-${collector.status}">${collector.status}</span>
                </p>
                <p class="text-sm">
                    <span class="font-medium">Vehicle:</span> 
                    ${collector.vehicle}
                </p>
                <p class="text-sm">
                    <span class="font-medium">Materials:</span><br>
                    ${collector.materials.join(', ')}
                </p>
                <p class="text-sm">
                    <span class="font-medium">Areas:</span><br>
                    ${collector.areas.join(', ')}
                </p>
                <button onclick="requestCollection(${collector.id})" 
                        class="mt-2 px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Request Collection
                </button>
            </div>
        `;
    }

    // Check if current user is a collector
    isCollector() {
        return sessionStorage.getItem('role') === 'collector';
    }

    // Start sending location updates (for collectors)
    startSendingLocation() {
        if (!navigator.geolocation) {
            console.error('Geolocation is not supported by this browser');
            return;
        }

        this.locationInterval = setInterval(() => {
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    try {
                        const response = await fetch('/Scrap/api/collectors/update_location.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                                status: sessionStorage.getItem('collectorStatus') || 'online'
                            })
                        });

                        if (!response.ok) {
                            throw new Error('Failed to update location');
                        }
                    } catch (error) {
                        console.error('Error updating location:', error);
                    }
                },
                (error) => {
                    console.error('Error getting location:', error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        }, this.options.updateInterval);
    }

    // Update collector status
    async updateStatus(status) {
        if (!this.isCollector()) {
            return;
        }

        sessionStorage.setItem('collectorStatus', status);

        try {
            const position = await this.getCurrentPosition();
            const response = await fetch('/Scrap/api/collectors/update_location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    status: status
                })
            });

            if (!response.ok) {
                throw new Error('Failed to update status');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            throw error;
        }
    }

    // Get current position as promise
    getCurrentPosition() {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            });
        });
    }
}