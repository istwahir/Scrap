-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'reward', 'request', 'system') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    reference_type VARCHAR(50),
    reference_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample notifications for existing users
INSERT INTO notifications (user_id, title, message, type, is_read) 
SELECT id, 'Welcome to Kiambu Recycling! 🎉', 'Start your recycling journey today and earn rewards for every collection.', 'info', FALSE
FROM users WHERE role = 'citizen' LIMIT 1;

INSERT INTO notifications (user_id, title, message, type, is_read) 
SELECT id, 'New Reward Available!', 'You can now redeem rewards with your points. Check out our reward catalog!', 'reward', FALSE
FROM users WHERE role = 'citizen' LIMIT 1;

INSERT INTO notifications (user_id, title, message, type, is_read) 
SELECT id, 'Earn Points', 'Submit your first recycling request to earn 50 points!', 'success', FALSE
FROM users WHERE role = 'citizen' LIMIT 1;
