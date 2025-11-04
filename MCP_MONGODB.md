# Model Context Protocol (MCP) - Kiambu Recycling Platform

## System Overview

The Kiambu Recycling Platform is a waste management system that connects citizens with waste collectors to facilitate efficient recycling and waste collection services in Kiambu County, Kenya.

## MongoDB Database Architecture

### Collections Structure

#### 1. **users** Collection
Primary collection for all system users (citizens, collectors, admins).

```javascript
{
  _id: ObjectId,
  name: String,
  email: String,
  phone: String,
  password: String,  // hashed
  role: String,  // enum: ['citizen', 'collector', 'admin']
  otp: String,
  otp_expires: Date,
  created_at: Date,
  updated_at: Date,
  
  // Indexes
  indexes: [
    { email: 1, unique: true },
    { phone: 1 },
    { role: 1 }
  ]
}
```

#### 2. **collector_applications** Collection
Stores applications from individuals wanting to become collectors.

```javascript
{
  _id: ObjectId,
  name: String,
  phone: String,
  id_number: String,
  date_of_birth: Date,
  address: String,
  location: {
    type: "Point",
    coordinates: [longitude, latitude]  // GeoJSON format
  },
  vehicle_type: String,  // enum: ['truck', 'pickup', 'tuktuk', 'motorcycle']
  vehicle_reg: String,
  documents: {
    id_card_front: String,
    id_card_back: String,
    vehicle_doc: String,
    good_conduct: String
  },
  service_areas: [String],
  materials_collected: [String],
  status: String,  // enum: ['pending', 'approved', 'rejected']
  status_notes: String,
  created_at: Date,
  updated_at: Date,
  
  // Indexes
  indexes: [
    { phone: 1 },
    { status: 1 },
    { location: "2dsphere" }
  ]
}
```

#### 3. **collectors** Collection
Active collectors linked to user accounts.

```javascript
{
  _id: ObjectId,
  application_id: ObjectId,  // ref: collector_applications
  user_id: ObjectId,  // ref: users
  email: String,
  status: String,  // enum: ['approved', 'pending', 'suspended', 'rejected']
  active_status: String,  // enum: ['online', 'offline', 'on_job']
  current_location: {
    type: "Point",
    coordinates: [longitude, latitude]
  },
  last_active: Date,
  rating: Number,  // 0.00 - 5.00
  total_collections: Number,
  total_earnings: Number,
  vehicle_type: String,
  vehicle_registration: String,
  materials_collected: [String],
  service_areas: [String],
  created_at: Date,
  updated_at: Date,
  
  // Indexes
  indexes: [
    { user_id: 1, unique: true },
    { email: 1, unique: true },
    { status: 1 },
    { active_status: 1 },
    { current_location: "2dsphere" }
  ]
}
```

#### 4. **collector_locations** Collection
Tracks historical location data for collectors.

```javascript
{
  _id: ObjectId,
  collector_id: ObjectId,  // ref: collectors
  location: {
    type: "Point",
    coordinates: [longitude, latitude]
  },
  recorded_at: Date,
  
  // Indexes
  indexes: [
    { collector_id: 1, recorded_at: -1 },
    { location: "2dsphere" }
  ]
}
```

#### 5. **dropoff_points** Collection
Designated recycling drop-off locations.

```javascript
{
  _id: ObjectId,
  name: String,
  location: {
    type: "Point",
    coordinates: [longitude, latitude]
  },
  address: String,
  materials: [String],  // ['plastic', 'paper', 'metal', 'glass', 'electronics']
  operating_hours: String,
  contact_phone: String,
  status: String,  // enum: ['active', 'inactive']
  collection_count: Number,
  created_at: Date,
  
  // Indexes
  indexes: [
    { location: "2dsphere" },
    { status: 1 },
    { materials: 1 }
  ]
}
```

#### 6. **collection_requests** Collection
Citizen requests for waste collection.

```javascript
{
  _id: ObjectId,
  user_id: ObjectId,  // ref: users
  collector_id: ObjectId,  // ref: collectors (nullable)
  dropoff_point_id: ObjectId,  // ref: dropoff_points (nullable)
  materials: [String],  // SET of materials
  material_notes: String,
  pickup_location: {
    type: "Point",
    coordinates: [longitude, latitude]
  },
  pickup_address: String,
  photo_url: String,
  estimated_weight: Number,
  pickup_date: Date,
  pickup_time: String,
  status: String,  // enum: ['pending', 'assigned', 'en_route', 'completed', 'cancelled']
  notes: String,
  accepted_at: Date,
  declined_at: Date,
  completed_at: Date,
  created_at: Date,
  updated_at: Date,
  
  // Indexes
  indexes: [
    { user_id: 1 },
    { collector_id: 1, status: 1 },
    { status: 1 },
    { created_at: -1 },
    { pickup_location: "2dsphere" }
  ]
}
```

#### 7. **collections** Collection
Completed collection records.

```javascript
{
  _id: ObjectId,
  request_id: ObjectId,  // ref: collection_requests
  collector_id: ObjectId,  // ref: collectors
  user_id: ObjectId,  // ref: users
  materials: [String],
  weight: Number,
  amount: Number,
  location: {
    type: "Point",
    coordinates: [longitude, latitude]
  },
  address: String,
  completed_at: Date,
  created_at: Date,
  updated_at: Date,
  
  // Indexes
  indexes: [
    { collector_id: 1, completed_at: -1 },
    { user_id: 1 },
    { request_id: 1 }
  ]
}
```

#### 8. **reviews** Collection
User reviews of collectors.

```javascript
{
  _id: ObjectId,
  collection_id: ObjectId,  // ref: collections
  collector_id: ObjectId,  // ref: collectors
  user_id: ObjectId,  // ref: users
  rating: Number,  // 1.00 - 5.00
  comment: String,
  created_at: Date,
  
  // Indexes
  indexes: [
    { collection_id: 1 },
    { collector_id: 1 },
    { user_id: 1 }
  ]
}
```

#### 9. **rewards** Collection
Points and rewards system.

```javascript
{
  _id: ObjectId,
  user_id: ObjectId,  // ref: users
  points: Number,
  activity_type: String,  // enum: ['collection', 'referral', 'bonus']
  reference_id: ObjectId,  // nullable
  redeemed: Boolean,
  redeemed_at: Date,
  created_at: Date,
  
  // Indexes
  indexes: [
    { user_id: 1 },
    { redeemed: 1 },
    { activity_type: 1 }
  ]
}
```

#### 10. **mpesa_transactions** Collection
M-Pesa payment transactions.

```javascript
{
  _id: ObjectId,
  user_id: ObjectId,  // ref: users
  amount: Number,
  phone: String,
  transaction_type: String,  // enum: ['reward_redemption', 'payment']
  mpesa_receipt: String,
  merchant_request_id: String,
  checkout_request_id: String,
  result_code: String,
  result_desc: String,
  status: String,  // enum: ['pending', 'completed', 'failed']
  created_at: Date,
  updated_at: Date,
  
  // Indexes
  indexes: [
    { user_id: 1 },
    { mpesa_receipt: 1 },
    { status: 1 },
    { created_at: -1 }
  ]
}
```

#### 11. **feedback** Collection
User feedback and complaints.

```javascript
{
  _id: ObjectId,
  user_id: ObjectId,  // ref: users
  request_id: ObjectId,  // ref: collection_requests (nullable)
  message: String,
  rating: Number,  // 1-5
  created_at: Date,
  
  // Indexes
  indexes: [
    { user_id: 1 },
    { request_id: 1 },
    { created_at: -1 }
  ]
}
```

#### 12. **notifications** Collection
System notifications for users.

```javascript
{
  _id: ObjectId,
  user_id: ObjectId,  // ref: users
  title: String,
  message: String,
  type: String,  // enum: ['info', 'success', 'warning', 'error']
  reference_type: String,  // e.g., 'collection_request', 'payment'
  reference_id: ObjectId,
  read: Boolean,
  created_at: Date,
  
  // Indexes
  indexes: [
    { user_id: 1, read: 1 },
    { created_at: -1 }
  ]
}
```

#### 13. **admin_logs** Collection
System activity logs for audit trail.

```javascript
{
  _id: ObjectId,
  admin_id: ObjectId,  // ref: users
  action: String,
  target_type: String,  // e.g., 'collector', 'request', 'user'
  target_id: ObjectId,
  details: Object,  // JSON object with action details
  ip_address: String,
  user_agent: String,
  created_at: Date,
  
  // Indexes
  indexes: [
    { admin_id: 1 },
    { target_type: 1, target_id: 1 },
    { created_at: -1 }
  ]
}
```

## API Endpoints

### Authentication
- `POST /api/register.php` - User registration
- `POST /api/login.php` - User login
- `POST /api/logout.php` - User logout
- `POST /api/request_otp.php` - Request OTP for verification
- `POST /api/verify_otp.php` - Verify OTP

### Citizens
- `GET /api/get_dropoffs.php` - Get nearby dropoff points
- `POST /api/create_request.php` - Create collection request
- `GET /api/get_user_requests.php` - Get user's requests
- `POST /api/cancel_request.php` - Cancel a request
- `GET /api/get_rewards.php` - Get user rewards
- `POST /api/redeem_reward.php` - Redeem reward points

### Collectors
- `POST /api/collectors/register.php` - Submit collector application
- `GET /api/collectors/dashboard.php` - Collector dashboard data
- `POST /api/collectors/accept_request.php` - Accept collection request
- `POST /api/collectors/decline_request.php` - Decline collection request
- `POST /api/collectors/complete_collection.php` - Complete collection
- `POST /api/collectors/update_location.php` - Update current location
- `GET /api/collectors/earnings.php` - Get earnings history

### Admin
- `GET /api/admin/dashboard.php` - Admin dashboard statistics
- `GET /api/admin/collectors.php` - Manage collectors
- `POST /api/admin/collectors.php` - Update collector status
- `GET /api/admin/requests.php` - View all requests
- `GET /api/admin/dropoffs.php` - Manage dropoff points
- `POST /api/admin/dropoffs.php` - Create/update dropoff point
- `GET /api/admin/rewards.php` - Manage rewards
- `GET /api/admin/reports.php` - Generate reports
- `GET /api/admin/trends.php` - Get trend data

## MongoDB Queries Examples

### Find Nearby Collectors
```javascript
db.collectors.find({
  status: "approved",
  active_status: { $in: ["online", "on_job"] },
  current_location: {
    $near: {
      $geometry: {
        type: "Point",
        coordinates: [36.8219, -1.2921]  // [lng, lat]
      },
      $maxDistance: 5000  // 5km radius
    }
  }
})
```

### Get Collection Statistics
```javascript
db.collection_requests.aggregate([
  {
    $match: {
      created_at: {
        $gte: new Date("2025-01-01"),
        $lte: new Date("2025-12-31")
      }
    }
  },
  {
    $group: {
      _id: "$status",
      count: { $sum: 1 },
      total_weight: { $sum: "$estimated_weight" }
    }
  }
])
```

### Calculate Collector Rating
```javascript
db.reviews.aggregate([
  {
    $match: { collector_id: ObjectId("collector_id_here") }
  },
  {
    $group: {
      _id: "$collector_id",
      average_rating: { $avg: "$rating" },
      total_reviews: { $sum: 1 }
    }
  }
])
```

### Find Top Collectors
```javascript
db.collectors.aggregate([
  {
    $match: { status: "approved" }
  },
  {
    $lookup: {
      from: "collections",
      localField: "_id",
      foreignField: "collector_id",
      as: "collections"
    }
  },
  {
    $project: {
      name: 1,
      total_collections: { $size: "$collections" },
      rating: 1,
      total_earnings: 1
    }
  },
  {
    $sort: { total_collections: -1 }
  },
  {
    $limit: 10
  }
])
```

### Materials Distribution
```javascript
db.collection_requests.aggregate([
  {
    $match: {
      status: { $in: ["completed", "en_route"] }
    }
  },
  {
    $unwind: "$materials"
  },
  {
    $group: {
      _id: "$materials",
      count: { $sum: 1 }
    }
  },
  {
    $sort: { count: -1 }
  }
])
```

## Real-time Features with MongoDB Change Streams

### Monitor Collection Requests
```javascript
const changeStream = db.collection_requests.watch([
  {
    $match: {
      operationType: { $in: ['insert', 'update'] }
    }
  }
]);

changeStream.on('change', (change) => {
  // Notify collectors via WebSocket
  notifyCollectors(change.fullDocument);
});
```

### Track Collector Locations
```javascript
const locationStream = db.collector_locations.watch([
  {
    $match: {
      operationType: 'insert'
    }
  }
]);

locationStream.on('change', (change) => {
  // Update real-time map
  updateCollectorPosition(change.fullDocument);
});
```

## Data Relationships

```
users (1) ─────┬───── (N) collection_requests
               │
               ├───── (N) rewards
               │
               ├───── (N) feedback
               │
               └───── (1) collectors

collectors (1) ─────┬───── (N) collections
                    │
                    ├───── (N) reviews
                    │
                    └───── (N) collector_locations

collection_requests (1) ─── (1) collections

collections (1) ────── (N) reviews
```

## Performance Optimization

### Compound Indexes
```javascript
// Fast collector lookup by area and status
db.collectors.createIndex({
  "service_areas": 1,
  "status": 1,
  "active_status": 1
});

// Efficient request filtering
db.collection_requests.createIndex({
  "status": 1,
  "created_at": -1
});

// Quick user reward calculation
db.rewards.createIndex({
  "user_id": 1,
  "redeemed": 1
});
```

### Geospatial Indexes
```javascript
// Enable proximity searches
db.collectors.createIndex({ "current_location": "2dsphere" });
db.dropoff_points.createIndex({ "location": "2dsphere" });
db.collection_requests.createIndex({ "pickup_location": "2dsphere" });
```

## Security Considerations

1. **Authentication**: Use bcrypt for password hashing
2. **Authorization**: Role-based access control (RBAC)
3. **Data Validation**: Mongoose schemas with validation
4. **Rate Limiting**: Prevent API abuse
5. **Input Sanitization**: Prevent NoSQL injection
6. **HTTPS**: Encrypt data in transit
7. **Backup**: Regular automated backups

## Deployment Architecture

```
┌──────────────┐     ┌──────────────┐
│ React Native │     │   Web App    │
│  Mobile App  │     │  (Citizens)  │
└──────┬───────┘     └──────┬───────┘
       │                    │
       └────────┬───────────┘
                │
         ┌──────▼──────┐
         │   REST API   │
         │   + Socket   │
         └──────┬───────┘
                │
       ┌────────▼────────┐
       │  Load Balancer  │
       └────────┬─────────┘
                │
           ┌────┴────┐
           │         │
      ┌────▼───┐  ┌──▼────┐
      │  App   │  │  App  │
      │ Server │  │Server │
      └────┬───┘  └──┬────┘
           │         │
           └────┬────┘
                │
         ┌──────▼────────┐
         │   MongoDB     │
         │ Replica Set   │
         └───────────────┘
```

## Environment Variables

```env
MONGODB_URI=
MONGODB_DB=kiambu_recycling
JWT_SECRET=your_jwt_secret_key
MPESA_CONSUMER_KEY=your_mpesa_key
MPESA_CONSUMER_SECRET=your_mpesa_secret
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey
```

## Migration from MySQL to MongoDB

### Key Changes

1. **Schema Flexibility**: No rigid schema, allows dynamic fields
2. **Relationships**: Embedded documents vs. joins
3. **Geospatial Queries**: Native support for location-based queries
4. **Scalability**: Horizontal scaling with sharding
5. **Performance**: Faster for read-heavy operations

### Benefits

- ✅ Better geospatial query performance
- ✅ Flexible schema for evolving requirements
- ✅ Native JSON support
- ✅ Horizontal scalability
- ✅ Real-time capabilities with Change Streams
- ✅ Aggregation pipeline for complex queries

---

# React Native Mobile Application

## Overview

The Kiambu Recycling mobile app provides citizens and collectors with a seamless mobile experience for managing waste collection requests, tracking collectors in real-time, and earning rewards.

## Technology Stack

### Core Framework
- **React Native** 0.73+
- **Expo** (optional, for managed workflow)
- **TypeScript** for type safety

### State Management
- **Redux Toolkit** or **Zustand** for global state
- **React Query** for server state & caching
- **AsyncStorage** for local persistence

### Navigation
- **React Navigation** 6+
  - Stack Navigator (main screens)
  - Bottom Tab Navigator (main app)
  - Drawer Navigator (settings)

### UI Components
- **React Native Paper** or **NativeBase**
- **React Native Elements**
- **React Native Vector Icons**
- **Lottie React Native** (animations)

### Maps & Location
- **React Native Maps** (Google Maps/Apple Maps)
- **@react-native-community/geolocation**
- **react-native-geocoding**

### Real-time Communication
- **Socket.io Client** for WebSocket connections
- **@notifee/react-native** for push notifications
- **Firebase Cloud Messaging** (FCM)

### Media & Files
- **React Native Image Picker**
- **React Native Camera** (optional)
- **React Native FS** for file system access

### Authentication & Security
- **JWT** for token management
- **React Native Keychain** for secure storage
- **React Native Biometrics** (fingerprint/face ID)

### Payments
- **React Native M-Pesa SDK** (custom bridge)
- **Daraja API** integration

## Project Structure

```
kiambu-recycling-app/
├── src/
│   ├── api/
│   │   ├── axios.config.ts
│   │   ├── auth.api.ts
│   │   ├── requests.api.ts
│   │   ├── collectors.api.ts
│   │   ├── rewards.api.ts
│   │   └── dropoffs.api.ts
│   ├── components/
│   │   ├── common/
│   │   │   ├── Button.tsx
│   │   │   ├── Input.tsx
│   │   │   ├── Card.tsx
│   │   │   ├── Loading.tsx
│   │   │   └── ErrorBoundary.tsx
│   │   ├── maps/
│   │   │   ├── CollectorMap.tsx
│   │   │   ├── DropoffMap.tsx
│   │   │   └── MarkerCluster.tsx
│   │   └── requests/
│   │       ├── RequestCard.tsx
│   │       ├── MaterialSelector.tsx
│   │       └── StatusBadge.tsx
│   ├── navigation/
│   │   ├── AppNavigator.tsx
│   │   ├── AuthNavigator.tsx
│   │   ├── CitizenNavigator.tsx
│   │   └── CollectorNavigator.tsx
│   ├── screens/
│   │   ├── auth/
│   │   │   ├── LoginScreen.tsx
│   │   │   ├── RegisterScreen.tsx
│   │   │   └── OTPVerificationScreen.tsx
│   │   ├── citizen/
│   │   │   ├── HomeScreen.tsx
│   │   │   ├── CreateRequestScreen.tsx
│   │   │   ├── RequestsScreen.tsx
│   │   │   ├── TrackCollectorScreen.tsx
│   │   │   ├── RewardsScreen.tsx
│   │   │   └── ProfileScreen.tsx
│   │   └── collector/
│   │       ├── DashboardScreen.tsx
│   │       ├── RequestsMapScreen.tsx
│   │       ├── ActiveJobScreen.tsx
│   │       ├── EarningsScreen.tsx
│   │       └── ProfileScreen.tsx
│   ├── store/
│   │   ├── index.ts
│   │   ├── slices/
│   │   │   ├── authSlice.ts
│   │   │   ├── requestsSlice.ts
│   │   │   ├── locationSlice.ts
│   │   │   └── notificationsSlice.ts
│   │   └── hooks.ts
│   ├── services/
│   │   ├── socket.service.ts
│   │   ├── location.service.ts
│   │   ├── notification.service.ts
│   │   ├── storage.service.ts
│   │   └── mpesa.service.ts
│   ├── hooks/
│   │   ├── useAuth.ts
│   │   ├── useLocation.ts
│   │   ├── useRequests.ts
│   │   └── useSocket.ts
│   ├── utils/
│   │   ├── constants.ts
│   │   ├── helpers.ts
│   │   ├── validators.ts
│   │   └── permissions.ts
│   ├── types/
│   │   ├── models.ts
│   │   ├── api.ts
│   │   └── navigation.ts
│   └── assets/
│       ├── images/
│       ├── icons/
│       └── animations/
├── android/
├── ios/
├── app.json
├── package.json
├── tsconfig.json
└── README.md
```

## Core Features Implementation

### 1. Authentication Flow

```typescript
// src/api/auth.api.ts
import axios from './axios.config';
import { LoginResponse, RegisterData, User } from '../types/api';

export const authAPI = {
  login: async (email: string, password: string): Promise<LoginResponse> => {
    const response = await axios.post('/api/login.php', { email, password });
    return response.data;
  },

  register: async (data: RegisterData): Promise<LoginResponse> => {
    const response = await axios.post('/api/register.php', data);
    return response.data;
  },

  requestOTP: async (phone: string): Promise<void> => {
    await axios.post('/api/request_otp.php', { phone });
  },

  verifyOTP: async (phone: string, otp: string): Promise<LoginResponse> => {
    const response = await axios.post('/api/verify_otp.php', { phone, otp });
    return response.data;
  },

  logout: async (): Promise<void> => {
    await axios.post('/api/logout.php');
  }
};
```

```typescript
// src/hooks/useAuth.ts
import { useState } from 'react';
import { useDispatch } from 'react-redux';
import { authAPI } from '../api/auth.api';
import { setUser, clearUser } from '../store/slices/authSlice';
import { StorageService } from '../services/storage.service';

export const useAuth = () => {
  const [loading, setLoading] = useState(false);
  const dispatch = useDispatch();

  const login = async (email: string, password: string) => {
    try {
      setLoading(true);
      const response = await authAPI.login(email, password);
      
      // Store token securely
      await StorageService.setToken(response.token);
      
      // Update Redux state
      dispatch(setUser(response.user));
      
      return response;
    } catch (error) {
      throw error;
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      await authAPI.logout();
      await StorageService.clearToken();
      dispatch(clearUser());
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  return { login, logout, loading };
};
```

### 2. Real-time Location Tracking

```typescript
// src/services/location.service.ts
import Geolocation from '@react-native-community/geolocation';
import { PermissionsAndroid, Platform } from 'react-native';

export class LocationService {
  static watchId: number | null = null;

  static async requestPermission(): Promise<boolean> {
    if (Platform.OS === 'android') {
      const granted = await PermissionsAndroid.request(
        PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION
      );
      return granted === PermissionsAndroid.RESULTS.GRANTED;
    }
    return true; // iOS permissions handled in Info.plist
  }

  static getCurrentPosition(): Promise<GeolocationPosition> {
    return new Promise((resolve, reject) => {
      Geolocation.getCurrentPosition(
        (position) => resolve(position),
        (error) => reject(error),
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
      );
    });
  }

  static startTracking(
    callback: (position: GeolocationPosition) => void,
    errorCallback?: (error: GeolocationError) => void
  ) {
    this.watchId = Geolocation.watchPosition(
      callback,
      errorCallback,
      { 
        enableHighAccuracy: true, 
        distanceFilter: 50, // Update every 50 meters
        interval: 10000 // Update every 10 seconds
      }
    );
  }

  static stopTracking() {
    if (this.watchId !== null) {
      Geolocation.clearWatch(this.watchId);
      this.watchId = null;
    }
  }
}
```

```typescript
// src/hooks/useLocation.ts
import { useState, useEffect } from 'react';
import { LocationService } from '../services/location.service';
import { Location } from '../types/models';

export const useLocation = (enableTracking = false) => {
  const [location, setLocation] = useState<Location | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (enableTracking) {
      startTracking();
    }

    return () => {
      LocationService.stopTracking();
    };
  }, [enableTracking]);

  const startTracking = async () => {
    const hasPermission = await LocationService.requestPermission();
    
    if (!hasPermission) {
      setError('Location permission denied');
      return;
    }

    LocationService.startTracking(
      (position) => {
        setLocation({
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          accuracy: position.coords.accuracy
        });
        setError(null);
      },
      (err) => {
        setError(err.message);
      }
    );
  };

  const getCurrentLocation = async () => {
    try {
      setLoading(true);
      const position = await LocationService.getCurrentPosition();
      setLocation({
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
        accuracy: position.coords.accuracy
      });
      return position;
    } catch (err: any) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  return { location, error, loading, getCurrentLocation, startTracking };
};
```

### 3. Real-time Updates with Socket.io

```typescript
// src/services/socket.service.ts
import io, { Socket } from 'socket.io-client';
import { SOCKET_URL } from '../utils/constants';

class SocketService {
  private socket: Socket | null = null;
  private listeners: Map<string, Function[]> = new Map();

  connect(userId: string, role: string) {
    if (this.socket?.connected) return;

    this.socket = io(SOCKET_URL, {
      auth: { userId, role },
      transports: ['websocket'],
      reconnection: true,
      reconnectionAttempts: 5,
      reconnectionDelay: 1000
    });

    this.socket.on('connect', () => {
      console.log('Socket connected');
    });

    this.socket.on('disconnect', () => {
      console.log('Socket disconnected');
    });

    this.socket.on('error', (error) => {
      console.error('Socket error:', error);
    });

    // Restore listeners
    this.listeners.forEach((callbacks, event) => {
      callbacks.forEach(callback => {
        this.socket?.on(event, callback);
      });
    });
  }

  disconnect() {
    if (this.socket) {
      this.socket.disconnect();
      this.socket = null;
    }
  }

  on(event: string, callback: Function) {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, []);
    }
    this.listeners.get(event)?.push(callback);

    if (this.socket) {
      this.socket.on(event, callback as any);
    }
  }

  off(event: string, callback?: Function) {
    if (callback) {
      const callbacks = this.listeners.get(event);
      if (callbacks) {
        const index = callbacks.indexOf(callback);
        if (index > -1) {
          callbacks.splice(index, 1);
        }
      }
      this.socket?.off(event, callback as any);
    } else {
      this.listeners.delete(event);
      this.socket?.off(event);
    }
  }

  emit(event: string, data: any) {
    this.socket?.emit(event, data);
  }

  // Specific methods
  updateLocation(location: { latitude: number; longitude: number }) {
    this.emit('update_location', location);
  }

  subscribeToRequestUpdates(callback: (request: any) => void) {
    this.on('request_updated', callback);
  }

  subscribeToCollectorLocation(callback: (location: any) => void) {
    this.on('collector_location', callback);
  }
}

export default new SocketService();
```

```typescript
// src/hooks/useSocket.ts
import { useEffect } from 'react';
import { useSelector } from 'react-redux';
import SocketService from '../services/socket.service';
import { RootState } from '../store';

export const useSocket = () => {
  const user = useSelector((state: RootState) => state.auth.user);

  useEffect(() => {
    if (user) {
      SocketService.connect(user._id, user.role);
    }

    return () => {
      SocketService.disconnect();
    };
  }, [user]);

  return SocketService;
};
```

### 4. Map with Real-time Collector Tracking

```typescript
// src/screens/citizen/TrackCollectorScreen.tsx
import React, { useState, useEffect } from 'react';
import { View, StyleSheet } from 'react-native';
import MapView, { Marker, Polyline } from 'react-native-maps';
import { useSocket } from '../../hooks/useSocket';
import { useLocation } from '../../hooks/useLocation';

interface TrackCollectorScreenProps {
  route: { params: { requestId: string; collectorId: string } };
}

export const TrackCollectorScreen: React.FC<TrackCollectorScreenProps> = ({
  route
}) => {
  const { requestId, collectorId } = route.params;
  const socket = useSocket();
  const { location: userLocation } = useLocation(true);
  
  const [collectorLocation, setCollectorLocation] = useState<{
    latitude: number;
    longitude: number;
  } | null>(null);

  const [routePath, setRoutePath] = useState<
    { latitude: number; longitude: number }[]
  >([]);

  useEffect(() => {
    // Subscribe to collector location updates
    socket.subscribeToCollectorLocation((data) => {
      if (data.collectorId === collectorId) {
        setCollectorLocation({
          latitude: data.location.coordinates[1],
          longitude: data.location.coordinates[0]
        });
        
        // Add to route path
        setRoutePath(prev => [...prev, {
          latitude: data.location.coordinates[1],
          longitude: data.location.coordinates[0]
        }]);
      }
    });

    return () => {
      socket.off('collector_location');
    };
  }, [collectorId]);

  if (!userLocation) {
    return <View style={styles.loading}><Text>Loading map...</Text></View>;
  }

  return (
    <View style={styles.container}>
      <MapView
        style={styles.map}
        initialRegion={{
          latitude: userLocation.latitude,
          longitude: userLocation.longitude,
          latitudeDelta: 0.01,
          longitudeDelta: 0.01
        }}
        showsUserLocation
        followsUserLocation
      >
        {/* User location marker */}
        <Marker
          coordinate={{
            latitude: userLocation.latitude,
            longitude: userLocation.longitude
          }}
          title="Your Location"
          pinColor="blue"
        />

        {/* Collector location marker */}
        {collectorLocation && (
          <Marker
            coordinate={collectorLocation}
            title="Collector"
            description="Your collector is on the way"
          >
            <View style={styles.collectorMarker}>
              <Image
                source={require('../../assets/icons/truck.png')}
                style={styles.markerIcon}
              />
            </View>
          </Marker>
        )}

        {/* Route polyline */}
        {routePath.length > 1 && (
          <Polyline
            coordinates={routePath}
            strokeColor="#4CAF50"
            strokeWidth={3}
          />
        )}
      </MapView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1
  },
  map: {
    flex: 1
  },
  loading: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center'
  },
  collectorMarker: {
    backgroundColor: 'white',
    padding: 5,
    borderRadius: 20,
    borderWidth: 2,
    borderColor: '#4CAF50'
  },
  markerIcon: {
    width: 30,
    height: 30
  }
});
```

### 5. Create Collection Request

```typescript
// src/screens/citizen/CreateRequestScreen.tsx
import React, { useState } from 'react';
import {
  View,
  ScrollView,
  StyleSheet,
  Alert,
  Image,
  TouchableOpacity
} from 'react-native';
import { Button, TextInput, Chip } from 'react-native-paper';
import { launchImageLibrary } from 'react-native-image-picker';
import { useLocation } from '../../hooks/useLocation';
import { requestsAPI } from '../../api/requests.api';

const MATERIALS = ['Plastic', 'Paper', 'Metal', 'Glass', 'Electronics'];

export const CreateRequestScreen: React.FC = ({ navigation }) => {
  const { location, getCurrentLocation } = useLocation();
  const [selectedMaterials, setSelectedMaterials] = useState<string[]>([]);
  const [address, setAddress] = useState('');
  const [notes, setNotes] = useState('');
  const [photo, setPhoto] = useState<string | null>(null);
  const [estimatedWeight, setEstimatedWeight] = useState('');
  const [loading, setLoading] = useState(false);

  const toggleMaterial = (material: string) => {
    setSelectedMaterials(prev =>
      prev.includes(material)
        ? prev.filter(m => m !== material)
        : [...prev, material]
    );
  };

  const handlePickImage = async () => {
    const result = await launchImageLibrary({
      mediaType: 'photo',
      quality: 0.8
    });

    if (result.assets && result.assets[0]) {
      setPhoto(result.assets[0].uri || null);
    }
  };

  const handleSubmit = async () => {
    if (selectedMaterials.length === 0) {
      Alert.alert('Error', 'Please select at least one material type');
      return;
    }

    if (!address.trim()) {
      Alert.alert('Error', 'Please enter your pickup address');
      return;
    }

    try {
      setLoading(true);

      // Get current location if not available
      let coords = location;
      if (!coords) {
        const position = await getCurrentLocation();
        coords = {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude
        };
      }

      const formData = new FormData();
      formData.append('materials', selectedMaterials.join(','));
      formData.append('address', address);
      formData.append('notes', notes);
      formData.append('latitude', coords.latitude.toString());
      formData.append('longitude', coords.longitude.toString());
      formData.append('estimated_weight', estimatedWeight);

      if (photo) {
        formData.append('photo', {
          uri: photo,
          type: 'image/jpeg',
          name: 'request_photo.jpg'
        } as any);
      }

      await requestsAPI.createRequest(formData);

      Alert.alert(
        'Success',
        'Your collection request has been submitted!',
        [
          {
            text: 'OK',
            onPress: () => navigation.goBack()
          }
        ]
      );
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Failed to create request');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Material Types</Text>
        <View style={styles.chipsContainer}>
          {MATERIALS.map(material => (
            <Chip
              key={material}
              selected={selectedMaterials.includes(material)}
              onPress={() => toggleMaterial(material)}
              style={styles.chip}
            >
              {material}
            </Chip>
          ))}
        </View>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Pickup Address</Text>
        <TextInput
          mode="outlined"
          value={address}
          onChangeText={setAddress}
          placeholder="Enter your full address"
          multiline
          numberOfLines={2}
        />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Estimated Weight (kg)</Text>
        <TextInput
          mode="outlined"
          value={estimatedWeight}
          onChangeText={setEstimatedWeight}
          placeholder="e.g., 5"
          keyboardType="numeric"
        />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Additional Notes (Optional)</Text>
        <TextInput
          mode="outlined"
          value={notes}
          onChangeText={setNotes}
          placeholder="Any special instructions..."
          multiline
          numberOfLines={3}
        />
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Photo (Optional)</Text>
        <TouchableOpacity
          style={styles.photoButton}
          onPress={handlePickImage}
        >
          {photo ? (
            <Image source={{ uri: photo }} style={styles.photoPreview} />
          ) : (
            <View style={styles.photoPlaceholder}>
              <Icon name="camera" size={40} color="#999" />
              <Text style={styles.photoText}>Add Photo</Text>
            </View>
          )}
        </TouchableOpacity>
      </View>

      <Button
        mode="contained"
        onPress={handleSubmit}
        loading={loading}
        disabled={loading}
        style={styles.submitButton}
      >
        Submit Request
      </Button>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
    padding: 16
  },
  section: {
    marginBottom: 20
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 10,
    color: '#333'
  },
  chipsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8
  },
  chip: {
    marginRight: 8,
    marginBottom: 8
  },
  photoButton: {
    borderWidth: 1,
    borderColor: '#ddd',
    borderRadius: 8,
    overflow: 'hidden',
    height: 200
  },
  photoPreview: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover'
  },
  photoPlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f9f9f9'
  },
  photoText: {
    marginTop: 10,
    color: '#999'
  },
  submitButton: {
    marginBottom: 30,
    paddingVertical: 8
  }
});
```

### 6. Push Notifications

```typescript
// src/services/notification.service.ts
import notifee, { AndroidImportance } from '@notifee/react-native';
import messaging from '@react-native-firebase/messaging';

export class NotificationService {
  static async requestPermission(): Promise<boolean> {
    const authStatus = await messaging().requestPermission();
    const enabled =
      authStatus === messaging.AuthorizationStatus.AUTHORIZED ||
      authStatus === messaging.AuthorizationStatus.PROVISIONAL;

    return enabled;
  }

  static async getFCMToken(): Promise<string> {
    const token = await messaging().getToken();
    return token;
  }

  static async displayNotification(
    title: string,
    body: string,
    data?: any
  ) {
    await notifee.requestPermission();

    const channelId = await notifee.createChannel({
      id: 'default',
      name: 'Default Channel',
      importance: AndroidImportance.HIGH
    });

    await notifee.displayNotification({
      title,
      body,
      data,
      android: {
        channelId,
        smallIcon: 'ic_launcher',
        pressAction: {
          id: 'default'
        }
      },
      ios: {
        sound: 'default'
      }
    });
  }

  static setupBackgroundHandler() {
    messaging().setBackgroundMessageHandler(async (remoteMessage) => {
      console.log('Background message:', remoteMessage);
      
      if (remoteMessage.notification) {
        await this.displayNotification(
          remoteMessage.notification.title || '',
          remoteMessage.notification.body || '',
          remoteMessage.data
        );
      }
    });
  }

  static setupForegroundHandler(
    handler: (message: any) => void
  ) {
    return messaging().onMessage(async (remoteMessage) => {
      handler(remoteMessage);
      
      if (remoteMessage.notification) {
        await this.displayNotification(
          remoteMessage.notification.title || '',
          remoteMessage.notification.body || '',
          remoteMessage.data
        );
      }
    });
  }
}
```

### 7. M-Pesa Payment Integration

```typescript
// src/services/mpesa.service.ts
import axios from '../api/axios.config';

export class MpesaService {
  static async initiateSTKPush(
    phone: string,
    amount: number,
    reference: string
  ): Promise<{ CheckoutRequestID: string }> {
    try {
      const response = await axios.post('/mpesa/mpesa_init.php', {
        phone,
        amount,
        reference,
        action: 'stk_push'
      });

      return response.data;
    } catch (error) {
      throw new Error('Failed to initiate payment');
    }
  }

  static async checkPaymentStatus(
    checkoutRequestID: string
  ): Promise<{ status: string; receipt?: string }> {
    try {
      const response = await axios.post('/mpesa/mpesa_init.php', {
        checkoutRequestID,
        action: 'query'
      });

      return response.data;
    } catch (error) {
      throw new Error('Failed to check payment status');
    }
  }

  static async redeemReward(points: number, phone: string) {
    try {
      const response = await axios.post('/api/redeem_reward.php', {
        points,
        phone
      });

      return response.data;
    } catch (error) {
      throw new Error('Failed to redeem reward');
    }
  }
}
```

## API Integration Examples

### Axios Configuration

```typescript
// src/api/axios.config.ts
import axios from 'axios';
import { StorageService } from '../services/storage.service';
import { API_BASE_URL } from '../utils/constants';

const axiosInstance = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Request interceptor
axiosInstance.interceptors.request.use(
  async (config) => {
    const token = await StorageService.getToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
axiosInstance.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token expired, logout user
      await StorageService.clearToken();
      // Navigate to login screen
    }
    return Promise.reject(error);
  }
);

export default axiosInstance;
```

### React Query Integration

```typescript
// src/hooks/useRequests.ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { requestsAPI } from '../api/requests.api';

export const useRequests = () => {
  const queryClient = useQueryClient();

  const { data: requests, isLoading } = useQuery({
    queryKey: ['requests'],
    queryFn: requestsAPI.getUserRequests,
    refetchInterval: 30000 // Refetch every 30 seconds
  });

  const createRequest = useMutation({
    mutationFn: requestsAPI.createRequest,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['requests'] });
    }
  });

  const cancelRequest = useMutation({
    mutationFn: requestsAPI.cancelRequest,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['requests'] });
    }
  });

  return {
    requests,
    isLoading,
    createRequest: createRequest.mutate,
    cancelRequest: cancelRequest.mutate
  };
};
```

## Environment Configuration

```typescript
// src/utils/constants.ts
import { Platform } from 'react-native';

export const API_BASE_URL = __DEV__
  ? Platform.OS === 'android'
    ? 'http://10.0.2.2:80'  // Android emulator
    : 'http://localhost:80'  // iOS simulator
  : 'https://api.kiamburecycling.com';

export const SOCKET_URL = __DEV__
  ? Platform.OS === 'android'
    ? 'http://10.0.2.2:8080'
    : 'http://localhost:8080'
  : 'https://socket.kiamburecycling.com';

export const GOOGLE_MAPS_API_KEY = 'YOUR_GOOGLE_MAPS_API_KEY';

export const COLORS = {
  primary: '#4CAF50',
  secondary: '#2196F3',
  error: '#F44336',
  warning: '#FF9800',
  success: '#4CAF50',
  background: '#F5F5F5',
  text: '#333333',
  textSecondary: '#666666'
};
```

## Dependencies

```json
{
  "dependencies": {
    "react": "18.2.0",
    "react-native": "0.73.0",
    "@react-navigation/native": "^6.1.9",
    "@react-navigation/stack": "^6.3.20",
    "@react-navigation/bottom-tabs": "^6.5.11",
    "@reduxjs/toolkit": "^2.0.1",
    "react-redux": "^9.0.4",
    "@tanstack/react-query": "^5.14.6",
    "axios": "^1.6.2",
    "socket.io-client": "^4.5.4",
    "react-native-maps": "^1.10.0",
    "@react-native-community/geolocation": "^3.1.0",
    "react-native-geocoding": "^0.5.0",
    "react-native-paper": "^5.11.3",
    "react-native-vector-icons": "^10.0.3",
    "react-native-image-picker": "^7.1.0",
    "@notifee/react-native": "^7.8.2",
    "@react-native-firebase/app": "^19.0.1",
    "@react-native-firebase/messaging": "^19.0.1",
    "react-native-keychain": "^8.1.2",
    "react-native-biometrics": "^3.0.1",
    "@react-native-async-storage/async-storage": "^1.21.0",
    "lottie-react-native": "^6.4.1",
    "react-native-reanimated": "^3.6.1",
    "react-native-gesture-handler": "^2.14.1"
  },
  "devDependencies": {
    "@types/react": "^18.2.45",
    "@types/react-native": "^0.73.0",
    "typescript": "^5.3.3",
    "@typescript-eslint/eslint-plugin": "^6.15.0",
    "@typescript-eslint/parser": "^6.15.0"
  }
}
```

## Setup Instructions

### 1. Initialize Project

```bash
# Using React Native CLI
npx react-native init KiambuRecyclingApp --template react-native-template-typescript

# OR using Expo
npx create-expo-app KiambuRecyclingApp --template expo-template-blank-typescript
```

### 2. Install Dependencies

```bash
cd KiambuRecyclingApp
npm install
```

### 3. iOS Setup

```bash
cd ios
pod install
cd ..
```

### 4. Android Configuration

Update `android/app/src/main/AndroidManifest.xml`:

```xml
<manifest>
  <uses-permission android:name="android.permission.INTERNET" />
  <uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
  <uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
  <uses-permission android:name="android.permission.CAMERA" />
  <uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE" />
  <uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE" />
</manifest>
```

### 5. iOS Configuration

Update `ios/KiambuRecyclingApp/Info.plist`:

```xml
<key>NSLocationWhenInUseUsageDescription</key>
<string>We need your location to find nearby collectors</string>
<key>NSLocationAlwaysUsageDescription</key>
<string>We need your location to track collection requests</string>
<key>NSCameraUsageDescription</key>
<string>We need camera access to take photos of waste</string>
<key>NSPhotoLibraryUsageDescription</key>
<string>We need photo library access to select images</string>
```

### 6. Run the App

```bash
# iOS
npm run ios

# Android
npm run android
```

## Testing Strategy

### Unit Tests
```typescript
// __tests__/services/location.service.test.ts
import { LocationService } from '../../src/services/location.service';

describe('LocationService', () => {
  it('should request location permission', async () => {
    const hasPermission = await LocationService.requestPermission();
    expect(typeof hasPermission).toBe('boolean');
  });

  it('should get current position', async () => {
    const position = await LocationService.getCurrentPosition();
    expect(position.coords.latitude).toBeDefined();
    expect(position.coords.longitude).toBeDefined();
  });
});
```

### Integration Tests
```typescript
// __tests__/api/auth.api.test.ts
import { authAPI } from '../../src/api/auth.api';

describe('Auth API', () => {
  it('should login with valid credentials', async () => {
    const response = await authAPI.login('test@example.com', 'password');
    expect(response.user).toBeDefined();
    expect(response.token).toBeDefined();
  });
});
```

### E2E Tests (Detox)
```typescript
// e2e/login.test.ts
describe('Login Flow', () => {
  it('should login successfully', async () => {
    await element(by.id('email-input')).typeText('test@example.com');
    await element(by.id('password-input')).typeText('password');
    await element(by.id('login-button')).tap();
    await expect(element(by.id('home-screen'))).toBeVisible();
  });
});
```

## Performance Optimization

1. **Image Optimization**: Use `react-native-fast-image` for caching
2. **List Performance**: Use `FlatList` with `windowSize` and `removeClippedSubviews`
3. **State Management**: Use selectors and memoization
4. **Bundle Size**: Enable Hermes engine
5. **Network**: Implement request caching with React Query

## Deployment

### Android (Play Store)

```bash
# Generate release APK
cd android
./gradlew assembleRelease

# Generate AAB (App Bundle)
./gradlew bundleRelease
```

### iOS (App Store)

```bash
# Archive the app
cd ios
xcodebuild -workspace KiambuRecyclingApp.xcworkspace \
  -scheme KiambuRecyclingApp \
  -archivePath build/KiambuRecyclingApp.xcarchive \
  archive
```

---

**Version**: 1.0.0  
**Last Updated**: November 3, 2025  
**System**: Kiambu Recycling Platform  
**Database**: MongoDB 7.0+  
**Mobile**: React Native 0.73+
