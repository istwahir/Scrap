-- Drop existing tables if they exist
DROP TABLE IF EXISTS mpesa_transactions;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS rewards;
DROP TABLE IF EXISTS collections;
DROP TABLE IF EXISTS collection_requests;
DROP TABLE IF EXISTS collector_locations;
DROP TABLE IF EXISTS collectors;
DROP TABLE IF EXISTS collector_materials;
DROP TABLE IF EXISTS collector_areas;
DROP TABLE IF EXISTS collector_applications;
DROP TABLE IF EXISTS dropoff_points;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    otp VARCHAR(6),
    otp_expires TIMESTAMP,
    role ENUM('citizen', 'collector', 'admin') DEFAULT 'citizen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Collector intake tables
CREATE TABLE collector_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    id_number VARCHAR(20) NOT NULL,
    date_of_birth DATE NOT NULL,
    address TEXT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    vehicle_type ENUM('truck', 'pickup', 'tuktuk', 'motorcycle') NOT NULL,
    vehicle_reg VARCHAR(20) NOT NULL,
    id_card_front VARCHAR(255) NOT NULL,
    id_card_back VARCHAR(255) NOT NULL,
    vehicle_doc VARCHAR(255) NOT NULL,
    good_conduct VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    status_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE collector_areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    area_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (application_id) REFERENCES collector_applications(id) ON DELETE CASCADE,
    UNIQUE KEY unique_collector_area (application_id, area_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE collector_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    material_type VARCHAR(20) NOT NULL,
    FOREIGN KEY (application_id) REFERENCES collector_applications(id) ON DELETE CASCADE,
    UNIQUE KEY unique_collector_material (application_id, material_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE collectors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL UNIQUE,
    email VARCHAR(100) DEFAULT NULL,
    UNIQUE KEY unique_collectors_email (email),
    active_status ENUM('online', 'offline', 'on_job') DEFAULT 'offline',
    current_latitude DECIMAL(10, 8),
    current_longitude DECIMAL(11, 8),
    last_active TIMESTAMP NULL,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    total_collections INT DEFAULT 0,
    total_earnings DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES collector_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_collectors_status (active_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE collector_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    collector_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (collector_id) REFERENCES collectors(id) ON DELETE CASCADE,
    INDEX idx_locations_collector_time (collector_id, recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create dropoff_points table
CREATE TABLE dropoff_points (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    lat DECIMAL(10, 8) NOT NULL,
    lng DECIMAL(11, 8) NOT NULL,
    address TEXT,
    materials SET('plastic', 'paper', 'metal', 'glass', 'electronics') NOT NULL,
    operating_hours VARCHAR(100),
    contact_phone VARCHAR(15),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE collection_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    collector_id INT DEFAULT NULL,
    dropoff_point_id INT DEFAULT NULL,
    material_type VARCHAR(100) NOT NULL,
    material_notes TEXT,
    address TEXT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    photo_url VARCHAR(255),
    weight_estimate DECIMAL(10,2),
    scheduled_for DATETIME DEFAULT NULL,
    status ENUM('pending', 'accepted', 'declined', 'on_route', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    accepted_at TIMESTAMP NULL DEFAULT NULL,
    declined_at TIMESTAMP NULL DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (collector_id) REFERENCES collectors(id),
    FOREIGN KEY (dropoff_point_id) REFERENCES dropoff_points(id),
    INDEX idx_requests_collector_status (collector_id, status),
    INDEX idx_requests_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create rewards table
CREATE TABLE rewards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    points INT DEFAULT 0,
    activity_type ENUM('collection', 'referral', 'bonus') NOT NULL,
    reference_id INT,
    redeemed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create mpesa_transactions table
CREATE TABLE mpesa_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    transaction_type ENUM('reward_redemption', 'payment') NOT NULL,
    mpesa_receipt VARCHAR(20),
    merchant_request_id VARCHAR(50),
    checkout_request_id VARCHAR(50),
    result_code VARCHAR(5),
    result_desc VARCHAR(255),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE collections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    collector_id INT NOT NULL,
    user_id INT NOT NULL,
    material_type VARCHAR(100) NOT NULL,
    weight DECIMAL(10,2) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    address TEXT NOT NULL,
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES collection_requests(id),
    FOREIGN KEY (collector_id) REFERENCES collectors(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_collections_collector_date (collector_id, completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    collection_id INT NOT NULL,
    collector_id INT NOT NULL,
    user_id INT NOT NULL,
    rating DECIMAL(3,2) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (collection_id) REFERENCES collections(id),
    FOREIGN KEY (collector_id) REFERENCES collectors(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    request_id INT,
    message TEXT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (request_id) REFERENCES collection_requests(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data ------------------------------------------------------------------

-- Insert sample admin user
INSERT INTO users (name, email, phone, password, role) VALUES
('Admin User', 'admin@kiamburecycling.com', '+254700000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample dropoff points
INSERT INTO dropoff_points (name, lat, lng, address, materials, operating_hours, contact_phone) VALUES
('Kiambu Town Recycling Center', -1.171315, 36.835372, 'Kiambu Town Main Street', 'plastic,paper,metal,glass', '08:00 - 17:00', '+254700000001'),
('Thika Road Mall Collection Point', -1.219543, 36.888123, 'TRM Drive, Roysambu', 'plastic,metal,electronics', '09:00 - 18:00', '+254700000002'),
('Ruaka Green Collection', -1.201234, 36.766789, 'Ruaka Town', 'plastic,paper,glass', '08:30 - 16:30', '+254700000003');

-- Insert sample collector applications
INSERT INTO collector_applications (name, phone, id_number, date_of_birth, address, latitude, longitude, vehicle_type, vehicle_reg, id_card_front, id_card_back, vehicle_doc, good_conduct, status, status_notes) VALUES
('John Kamau', '+254711111111', '12345678', '1990-04-12', 'Kiambu Road, House 123', -1.170500, 36.834900, 'pickup', 'KBZ 123A', 'ids/john_front.jpg', 'ids/john_back.jpg', 'vehicles/john_vehicle.pdf', 'certs/john_conduct.pdf', 'approved', 'Seed data approval'),
('Mary Wanjiku', '+254722222222', '23456789', '1988-08-03', 'Thindigua Estate, Apt 45', -1.217200, 36.887900, 'tuktuk', 'KCB 456B', 'ids/mary_front.jpg', 'ids/mary_back.jpg', 'vehicles/mary_vehicle.pdf', 'certs/mary_conduct.pdf', 'approved', 'Seed data approval'),
('Peter Omondi', '+254733333333', '34567890', '1985-01-25', 'Ruaka Heights, Block B', -1.201500, 36.767200, 'truck', 'KDE 789C', 'ids/peter_front.jpg', 'ids/peter_back.jpg', 'vehicles/peter_vehicle.pdf', 'certs/peter_conduct.pdf', 'approved', 'Seed data approval');

-- Associate applications with service areas
INSERT INTO collector_areas (application_id, area_name) VALUES
((SELECT id FROM collector_applications WHERE name = 'John Kamau'), 'Kiambu Town'),
((SELECT id FROM collector_applications WHERE name = 'John Kamau'), 'Ruaka'),
((SELECT id FROM collector_applications WHERE name = 'John Kamau'), 'Ndenderu'),
((SELECT id FROM collector_applications WHERE name = 'Mary Wanjiku'), 'Thindigua'),
((SELECT id FROM collector_applications WHERE name = 'Mary Wanjiku'), 'Kiambu Town'),
((SELECT id FROM collector_applications WHERE name = 'Mary Wanjiku'), 'Ruaka'),
((SELECT id FROM collector_applications WHERE name = 'Peter Omondi'), 'Ruaka'),
((SELECT id FROM collector_applications WHERE name = 'Peter Omondi'), 'Banana'),
((SELECT id FROM collector_applications WHERE name = 'Peter Omondi'), 'Muchatha');

-- Associate applications with preferred materials
INSERT INTO collector_materials (application_id, material_type) VALUES
((SELECT id FROM collector_applications WHERE name = 'John Kamau'), 'plastic'),
((SELECT id FROM collector_applications WHERE name = 'John Kamau'), 'metal'),
((SELECT id FROM collector_applications WHERE name = 'John Kamau'), 'paper'),
((SELECT id FROM collector_applications WHERE name = 'Mary Wanjiku'), 'plastic'),
((SELECT id FROM collector_applications WHERE name = 'Mary Wanjiku'), 'glass'),
((SELECT id FROM collector_applications WHERE name = 'Mary Wanjiku'), 'electronics'),
((SELECT id FROM collector_applications WHERE name = 'Peter Omondi'), 'metal'),
((SELECT id FROM collector_applications WHERE name = 'Peter Omondi'), 'paper'),
((SELECT id FROM collector_applications WHERE name = 'Peter Omondi'), 'plastic'),
((SELECT id FROM collector_applications WHERE name = 'Peter Omondi'), 'glass');

-- Insert sample users for collectors and citizens
INSERT INTO users (name, email, phone, password, role) VALUES
('John Kamau', 'john.kamau@kiamburecycling.com', '+254711111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'collector'),
('Mary Wanjiku', 'mary.wanjiku@kiamburecycling.com', '+254722222222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'collector'),
('Peter Omondi', 'peter.omondi@kiamburecycling.com', '+254733333333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'collector'),
('Alice Citizen', 'alice.citizen@example.com', '+254744444444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'citizen'),
('Brian Citizen', 'brian.citizen@example.com', '+254755555555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'citizen');

-- Activate collectors (include email for direct collector login)
INSERT INTO collectors (application_id, user_id, email, active_status, current_latitude, current_longitude, last_active, rating, total_collections, total_earnings) VALUES
((SELECT id FROM collector_applications WHERE name = 'John Kamau'), (SELECT id FROM users WHERE email = 'john.kamau@kiamburecycling.com'), 'john.kamau@kiamburecycling.com', 'online', -1.170500, 36.834900, NOW() - INTERVAL 10 MINUTE, 4.50, 25, 2450.00),
((SELECT id FROM collector_applications WHERE name = 'Mary Wanjiku'), (SELECT id FROM users WHERE email = 'mary.wanjiku@kiamburecycling.com'), 'mary.wanjiku@kiamburecycling.com', 'on_job', -1.217200, 36.887900, NOW() - INTERVAL 5 MINUTE, 4.80, 31, 3105.00),
((SELECT id FROM collector_applications WHERE name = 'Peter Omondi'), (SELECT id FROM users WHERE email = 'peter.omondi@kiamburecycling.com'), 'peter.omondi@kiamburecycling.com', 'offline', -1.201500, 36.767200, NOW() - INTERVAL 35 MINUTE, 4.20, 18, 1980.00);

-- Insert sample collection requests
INSERT INTO collection_requests (user_id, collector_id, dropoff_point_id, material_type, material_notes, address, latitude, longitude, photo_url, weight_estimate, scheduled_for, status, notes, accepted_at, declined_at, completed_at) VALUES
((SELECT id FROM users WHERE email = 'alice.citizen@example.com'), (SELECT id FROM collectors WHERE user_id = (SELECT id FROM users WHERE email = 'john.kamau@kiamburecycling.com')), 1, 'plastic', 'PET bottles and packaging', 'Kiambu Road, House 123', -1.170500, 36.834900, 'collections/request1.jpg', 5.5, '2024-10-08 09:00:00', 'completed', 'Handled promptly', '2024-10-07 16:00:00', NULL, '2024-10-08 09:45:00'),
((SELECT id FROM users WHERE email = 'brian.citizen@example.com'), (SELECT id FROM collectors WHERE user_id = (SELECT id FROM users WHERE email = 'mary.wanjiku@kiamburecycling.com')), 2, 'electronics', 'Old laptop and chargers', 'Thindigua Estate, Apt 45', -1.217200, 36.887900, 'collections/request2.jpg', 3.2, '2024-10-08 11:00:00', 'on_route', 'Collector en route', '2024-10-08 09:30:00', NULL, NULL),
((SELECT id FROM users WHERE email = 'alice.citizen@example.com'), NULL, NULL, 'paper', 'Shredded office paper', 'Kiambu Town Hall', -1.173000, 36.835800, 'collections/request3.jpg', 2.0, '2024-10-09 10:30:00', 'pending', 'Awaiting collector assignment', NULL, NULL, NULL);

-- Insert completed collections
INSERT INTO collections (request_id, collector_id, user_id, material_type, weight, amount, address, latitude, longitude, completed_at) VALUES
((SELECT id FROM collection_requests WHERE photo_url = 'collections/request1.jpg'), (SELECT id FROM collectors WHERE user_id = (SELECT id FROM users WHERE email = 'john.kamau@kiamburecycling.com')), (SELECT id FROM users WHERE email = 'alice.citizen@example.com'), 'plastic', 5.2, 520.00, 'Kiambu Road, House 123', -1.170500, 36.834900, '2024-10-08 09:45:00');

-- Collect citizen feedback
INSERT INTO reviews (collection_id, collector_id, user_id, rating, comment) VALUES
((SELECT id FROM collections WHERE request_id = (SELECT id FROM collection_requests WHERE photo_url = 'collections/request1.jpg')), (SELECT id FROM collectors WHERE user_id = (SELECT id FROM users WHERE email = 'john.kamau@kiamburecycling.com')), (SELECT id FROM users WHERE email = 'alice.citizen@example.com'), 4.5, 'Very professional and punctual. Handled the materials carefully.');

-- Seed rewards, Mpesa transactions, and feedback records
INSERT INTO rewards (user_id, points, activity_type, reference_id, redeemed) VALUES
((SELECT id FROM users WHERE email = 'alice.citizen@example.com'), 50, 'collection', (SELECT id FROM collection_requests WHERE photo_url = 'collections/request1.jpg'), FALSE),
((SELECT id FROM users WHERE email = 'brian.citizen@example.com'), 30, 'collection', (SELECT id FROM collection_requests WHERE photo_url = 'collections/request2.jpg'), FALSE);

INSERT INTO mpesa_transactions (user_id, amount, phone, transaction_type, mpesa_receipt, status) VALUES
((SELECT id FROM users WHERE email = 'alice.citizen@example.com'), 520.00, '+254744444444', 'reward_redemption', 'QWE12345TY', 'completed');

INSERT INTO feedback (user_id, request_id, message, rating) VALUES
((SELECT id FROM users WHERE email = 'alice.citizen@example.com'), (SELECT id FROM collection_requests WHERE photo_url = 'collections/request1.jpg'), 'Smooth experience and quick pickup.', 5),
((SELECT id FROM users WHERE email = 'brian.citizen@example.com'), (SELECT id FROM collection_requests WHERE photo_url = 'collections/request2.jpg'), 'Looking forward to timely pickup.', 4);

