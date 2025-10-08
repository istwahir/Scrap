-- Drop existing tables if they exist
DROP TABLE IF EXISTS mpesa_transactions;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS rewards;
DROP TABLE IF EXISTS collection_requests;
DROP TABLE IF EXISTS dropoff_points;
DROP TABLE IF EXISTS collectors;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    otp VARCHAR(6),
    otp_expires TIMESTAMP,
    role ENUM('citizen', 'collector', 'admin') DEFAULT 'citizen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create collectors table
CREATE TABLE collectors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    license_file VARCHAR(255) NOT NULL,
    id_number VARCHAR(20) NOT NULL,
    vehicle_type VARCHAR(50) NOT NULL,
    vehicle_registration VARCHAR(20) NOT NULL,
    materials_collected JSON NOT NULL,
    service_areas JSON NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    rating DECIMAL(3,2) DEFAULT 0.00,
    status ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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

-- Create collection_requests table
CREATE TABLE collection_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    collector_id INT,
    dropoff_point_id INT,
    materials SET('plastic', 'paper', 'metal', 'glass', 'electronics') NOT NULL,
    photo_url VARCHAR(255),
    estimated_weight DECIMAL(5,2),
    pickup_address TEXT NOT NULL,
    pickup_date DATE,
    pickup_time TIME,
    status ENUM('pending', 'assigned', 'en_route', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (collector_id) REFERENCES collectors(id),
    FOREIGN KEY (dropoff_point_id) REFERENCES dropoff_points(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create rewards table
CREATE TABLE rewards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    points INT DEFAULT 0,
    activity_type ENUM('collection', 'referral', 'bonus') NOT NULL,
    reference_id INT, -- collection_request_id or other reference
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

-- Create collections table
CREATE TABLE collections (
    collection_id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    collector_id INT NOT NULL,
    user_id INT NOT NULL,
    dropoff_point_id INT NOT NULL,
    collection_date TIMESTAMP NOT NULL,
    material_type SET('plastic', 'paper', 'metal', 'glass', 'electronics') NOT NULL,
    weight DECIMAL(10,2) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'accepted', 'en_route', 'completed', 'declined') DEFAULT 'pending',
    rating DECIMAL(3,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES collection_requests(id),
    FOREIGN KEY (collector_id) REFERENCES collectors(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (dropoff_point_id) REFERENCES dropoff_points(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create reviews table
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    collection_id INT NOT NULL,
    collector_id INT NOT NULL,
    user_id INT NOT NULL,
    rating DECIMAL(3,2) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (collection_id) REFERENCES collections(collection_id),
    FOREIGN KEY (collector_id) REFERENCES collectors(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create feedback table
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

-- Insert sample admin user
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@kiamburecycling.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample dropoff points
INSERT INTO dropoff_points (name, lat, lng, address, materials, operating_hours, contact_phone) VALUES
('Kiambu Town Recycling Center', -1.171315, 36.835372, 'Kiambu Town Main Street', 'plastic,paper,metal,glass', '8:00 AM - 5:00 PM', '+254700000001'),
('Thika Road Mall Collection Point', -1.219543, 36.888123, 'TRM Drive, Roysambu', 'plastic,metal,electronics', '9:00 AM - 6:00 PM', '+254700000002'),
('Ruaka Green Collection', -1.201234, 36.766789, 'Ruaka Town', 'plastic,paper,glass', '8:30 AM - 4:30 PM', '+254700000003');

-- Insert sample collectors with their users
INSERT INTO users (name, email, password, role) VALUES
('John Kamau', 'john.kamau@kiamburecycling.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'collector'),
('Mary Wanjiku', 'mary.wanjiku@kiamburecycling.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'collector'),
('Peter Omondi', 'peter.omondi@kiamburecycling.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'collector');

INSERT INTO collectors (user_id, name, phone, license_file, id_number, vehicle_type, vehicle_registration, materials_collected, service_areas, verified, rating, status) VALUES
(
    (SELECT id FROM users WHERE email = 'john.kamau@kiamburecycling.com'),
    'John Kamau',
    '+254711111111',
    'licenses/john_license.pdf',
    '12345678',
    'Pickup Truck',
    'KBZ 123A',
    '["plastic", "metal", "paper"]',
    '["Kiambu Town", "Ruaka", "Ndenderu"]',
    TRUE,
    4.5,
    'approved'
),
(
    (SELECT id FROM users WHERE email = 'mary.wanjiku@kiamburecycling.com'),
    'Mary Wanjiku',
    '+254722222222',
    'licenses/mary_license.pdf',
    '23456789',
    'Van',
    'KCB 456B',
    '["plastic", "glass", "electronics"]',
    '["Thindigua", "Kiambu Town", "Ruaka"]',
    TRUE,
    4.8,
    'approved'
),
(
    (SELECT id FROM users WHERE email = 'peter.omondi@kiamburecycling.com'),
    'Peter Omondi',
    '+254733333333',
    'licenses/peter_license.pdf',
    '34567890',
    'Truck',
    'KDE 789C',
    '["metal", "paper", "plastic", "glass"]',
    '["Ruaka", "Banana", "Muchatha"]',
    TRUE,
    4.2,
    'approved'
);

-- Insert sample collection requests
INSERT INTO collection_requests (user_id, collector_id, dropoff_point_id, materials, photo_url, estimated_weight, pickup_address, pickup_date, pickup_time, status) VALUES
((SELECT id FROM users WHERE email = 'admin@kiamburecycling.com'), (SELECT id FROM collectors WHERE phone = '+254711111111'), 1, 'plastic,metal', 'collections/request1.jpg', 5.5, 'Kiambu Road, House 123', '2025-10-08', '09:00:00', 'completed'),
((SELECT id FROM users WHERE email = 'admin@kiamburecycling.com'), (SELECT id FROM collectors WHERE phone = '+254722222222'), 2, 'plastic,electronics', 'collections/request2.jpg', 3.2, 'Thindigua Estate, Apt 45', '2025-10-08', '11:00:00', 'completed'),
((SELECT id FROM users WHERE email = 'admin@kiamburecycling.com'), (SELECT id FROM collectors WHERE phone = '+254733333333'), 3, 'paper,glass', 'collections/request3.jpg', 4.8, 'Ruaka Heights, Block B', '2025-10-08', '14:00:00', 'completed');

-- Insert sample collections
INSERT INTO collections (request_id, collector_id, user_id, dropoff_point_id, collection_date, material_type, weight, amount, status, rating) VALUES
(1, (SELECT id FROM collectors WHERE phone = '+254711111111'), (SELECT id FROM users WHERE email = 'admin@kiamburecycling.com'), 1, '2025-10-08 09:30:00', 'plastic,metal', 5.5, 550.00, 'completed', 4.5),
(2, (SELECT id FROM collectors WHERE phone = '+254722222222'), (SELECT id FROM users WHERE email = 'admin@kiamburecycling.com'), 2, '2025-10-08 11:30:00', 'plastic,electronics', 3.2, 640.00, 'completed', 5.0),
(3, (SELECT id FROM collectors WHERE phone = '+254733333333'), (SELECT id FROM users WHERE email = 'admin@kiamburecycling.com'), 3, '2025-10-08 14:30:00', 'paper,glass', 4.8, 384.00, 'completed', 4.0);

-- Insert sample reviews
INSERT INTO reviews (collection_id, collector_id, user_id, rating, comment) VALUES
(1, (SELECT id FROM collectors WHERE phone = '+254711111111'), (SELECT id FROM users WHERE email = 'admin@kiamburecycling.com'), 4.5, 'Very professional and punctual. Handled the materials carefully.'),
(2, (SELECT id FROM collectors WHERE phone = '+254722222222'), (SELECT id FROM users WHERE email = 'admin@kiamburecycling.com'), 5.0, 'Excellent service! Mary was very helpful and efficient.'),
(3, (SELECT id FROM collectors WHERE phone = '+254733333333'), (SELECT id FROM users WHERE email = 'admin@kiamburecycling.com'), 4.0, 'Good service overall, but arrived a bit late.');
