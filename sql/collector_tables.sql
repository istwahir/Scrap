-- Collector Applications Table
CREATE TABLE IF NOT EXISTS collector_applications (
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
);

-- Collector Areas Table
CREATE TABLE IF NOT EXISTS collector_areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    area_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (application_id) REFERENCES collector_applications(id) ON DELETE CASCADE,
    UNIQUE KEY unique_collector_area (application_id, area_name)
);

-- Collector Materials Table
CREATE TABLE IF NOT EXISTS collector_materials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    material_type VARCHAR(20) NOT NULL,
    FOREIGN KEY (application_id) REFERENCES collector_applications(id) ON DELETE CASCADE,
    UNIQUE KEY unique_collector_material (application_id, material_type)
);

-- Active Collectors Table (Created after approval)
CREATE TABLE IF NOT EXISTS collectors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL UNIQUE,
    user_id INT NOT NULL UNIQUE,
    active_status ENUM('online', 'offline', 'on_job') DEFAULT 'offline',
    current_latitude DECIMAL(10, 8),
    current_longitude DECIMAL(11, 8),
    last_active TIMESTAMP,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    total_collections INT DEFAULT 0,
    total_earnings DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (application_id) REFERENCES collector_applications(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Collector Location History
CREATE TABLE IF NOT EXISTS collector_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    collector_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (collector_id) REFERENCES collectors(id) ON DELETE CASCADE
);