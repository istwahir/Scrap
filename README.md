# Kiambu Recycling & Scraps Platform

A Progressive Web Application (PWA) for managing recycling and scrap collection in Kiambu County. Built with PHP, MySQL, and vanilla JavaScript.

## Features

- 📱 OTP-based authentication via phone number
- 🗺️ Interactive map showing recycling drop-off points
- 📦 Request recycling pickup service
- 💰 M-Pesa integration for reward redemption
- 👨‍💼 Collector dashboard for managing pickups
- ⚡ Works offline (PWA)
- 📊 Admin analytics and reporting

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP, WAMP, or any PHP server
- SSL certificate (for PWA features in production)

## Installation

1. Clone or download this repository to your web server's root directory:
   ```bash
   # For XAMPP on macOS
   cd /Applications/XAMPP/xamppfiles/htdocs/
   git clone <repository-url> Scrap
   ```

2. Create a MySQL database named 'kiambu_recycling':
   ```sql
   CREATE DATABASE kiambu_recycling;
   ```

3. Import the database schema and sample data:
   ```bash
   mysql -u root -p kiambu_recycling < sql/schema.sql
   mysql -u root -p kiambu_recycling < sql/seed_data.sql
   ```

4. Copy `config.example.php` to `config.php` and update the credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'kiambu_recycling');
   ```

5. Configure M-Pesa API credentials in config.php:
   ```php
   define('MPESA_CONSUMER_KEY', 'your_consumer_key');
   define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
   define('MPESA_PASSKEY', 'your_passkey');
   define('MPESA_SHORTCODE', 'your_shortcode');
   ```

## Database Setup

The application uses MySQL with the following tables:
- `users` - User accounts with OTP authentication
- `collectors` - Collector profiles and verification
- `dropoff_points` - Recycling collection points
- `collection_requests` - Pickup requests
- `rewards` - Points and reward system
- `collections` - Completed collection records
- `reviews` - User feedback and ratings
- `mpesa_transactions` - Payment transactions
- `feedback` - General user feedback

## Running Locally

### Option 1: XAMPP/WAMP (Recommended)
1. Start XAMPP/WAMP server
2. Access the application:
   ```
   http://localhost/Scrap/
   ```

### Option 2: PHP Built-in Server
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/Scrap
php -S localhost:8000
```

### Option 3: Docker (Advanced)
```bash
# If you have Docker installed
docker run -p 8080:80 -v $(pwd):/var/www/html php:8.1-apache
```

## Project Structure

```
/ (root)
│── index.php                 # Landing page with authentication modal
│── config.php                # Configuration settings
│── /controllers              # PHP controllers
│     ├── AuthController.php  # Authentication logic
│── /models                   # Data models
│     ├── User.php           # User model
│     ├── Collector.php      # Collector model
│     ├── Request.php        # Collection request model
│     ├── Reward.php         # Reward/points model
│     ├── Dropoff.php        # Drop-off point model
│── login.html               # Login page
│── signup.html              # Registration page
│── dashboard.html           # User dashboard
│── map.html                 # Interactive map
│── request.html             # Create pickup request
│── reward.html              # Points and rewards
│── guide.html               # Recycling guide
│── tracking.html            # Live tracking
│── manifest.json            # PWA manifest
│── service-worker.js        # Service worker
│── /public                  # Additional static assets
│     ├── /js                # JavaScript files
│── /sql                      # Database scripts
│     ├── schema.sql         # Database schema
│     ├── seed_data.sql      # Sample data
│── /includes                 # PHP includes
│     ├── header.php         # HTML header with navigation
│     ├── footer.php         # HTML footer
│     ├── auth.php           # Authentication helpers
│── /mpesa                    # M-Pesa integration
│── /api                      # API endpoints
│     ├── request_otp.php    # Request OTP
│     ├── verify_otp.php     # Verify OTP
│     ├── logout.php         # Logout
│     ├── get_rewards.php    # Get user rewards
│     ├── redeem_reward.php  # Redeem rewards
│     ├── /collectors        # Collector-specific endpoints
```

## API Endpoints

### Authentication
- `POST /api/request_otp.php` - Request OTP for phone number
- `POST /api/verify_otp.php` - Verify OTP and create session
- `POST /api/logout.php` - Logout user

### Dropoffs & Map
- `GET /api/get_dropoffs.php?lat=&lng=&materials=` - Get nearby dropoff points
- `GET /api/get_dropoff.php?id=` - Get specific dropoff point

### Collection Requests
- `POST /api/create_request.php` - Create new pickup request
- `GET /api/get_requests.php?user_id=` - Get user requests
- `POST /api/update_request_status.php` - Update request status

### Rewards & Points
- `GET /api/get_rewards.php` - Get user reward statistics
- `POST /api/redeem_reward.php` - Redeem points for rewards

### Collector Endpoints
- `GET /api/collectors/dashboard.php` - Get collector dashboard data
- `POST /api/collectors/accept_request.php` - Accept collection request
- `POST /api/collectors/decline_request.php` - Decline collection request
- `POST /api/collectors/complete_collection.php` - Mark collection as complete
- `POST /api/collectors/update_location.php` - Update collector location
- `GET /api/collectors/get_locations.php` - Get active collector locations

### Admin Endpoints
- `GET /api/admin/dashboard.php` - Admin dashboard statistics
- `GET /api/admin/trends.php` - Analytics and trends

### Real-time & Events
- `GET /api/events.php` - Server-sent events for real-time updates
- `GET /api/updates.php` - Polling endpoint for updates

### M-Pesa Integration
- `POST /api/mpesa_stkpush.php` - Initiate M-Pesa STK push
- `POST /api/mpesa_callback.php` - Handle M-Pesa payment callback

## Security Considerations

- All user inputs are sanitized using `filter_input()` and `htmlspecialchars()`
- CSRF tokens are required for forms
- File uploads are validated for type and size
- API keys are stored in config.php (never exposed to frontend)
- OTP verification for authentication
- Session-based authentication

## User Roles & Testing

### User Roles
- **Citizens (Users)** - Can request pickups, view rewards, track impact
- **Collectors** - Can accept requests, update locations, manage collections
- **Admins** - Can approve collectors, manage drop-off points, view analytics

### Test Accounts
- **Admin**: Phone `+254700000000` (already in database)
- **Collector**: Phone `+254711111111` (John Kamau)
- **User**: Create new account with any valid Kenyan phone number

### Development Mode Features
- OTP codes are displayed in browser alerts for testing
- Mock SMS sending (no real SMS sent)
- CORS enabled for local development
- Detailed error logging

## PWA Features

- **Offline Support** - Service worker caches pages and assets
- **Add to Home Screen** - Install prompt and manifest configuration
- **Push Notifications** - Real-time updates (when implemented)
- **Responsive Design** - Works on mobile, tablet, and desktop
- **Fast Loading** - Cached assets and optimized performance

## Production Deployment

1. **SSL Certificate** - Required for PWA features
2. **Environment Variables** - Set `ENV = 'production'` in config.php
3. **M-Pesa Integration** - Configure real API credentials
4. **SMS Gateway** - Replace mock SMS with real service
5. **File Uploads** - Configure secure file storage
6. **Database Optimization** - Add indexes for performance

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

MIT License. See LICENSE file for details.
