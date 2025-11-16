<?php
require_once __DIR__ . '/../config.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Request OTP for email address
     * @param string $email Email address
     * @return array Response with status and message
     */
    public function requestOTP($email) {
        try {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid email address format'
                ];
            }

            // Generate OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // Check if user exists
            $stmt = $this->db->prepare(
                "SELECT id FROM users WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Update existing user's OTP
                $stmt = $this->db->prepare(
                    "UPDATE users SET otp = ?, otp_expires = ? WHERE email = ?"
                );
                $stmt->execute([$otp, $expires, $email]);
            } else {
                // Create new user with OTP
                $stmt = $this->db->prepare(
                    "INSERT INTO users (email, otp, otp_expires) VALUES (?, ?, ?)"
                );
                $stmt->execute([$email, $otp, $expires]);
            }

            // In development, return OTP in response
            if (MOCK_EMAIL) {
                return [
                    'status' => 'success',
                    'message' => 'OTP sent successfully',
                    'debug_otp' => $otp // Remove in production
                ];
            }

            // In production, send OTP via email
            // $this->sendEmail($email, "Your OTP is: " . $otp);

            return [
                'status' => 'success',
                'message' => 'OTP sent successfully'
            ];

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to generate OTP'
            ];
        }
    }
    
    /**
     * Verify OTP and create session
     * @param string $email Email address
     * @param string $otp OTP code
     * @return array Response with status and user data if successful
     */
    public function verifyOTP($email, $otp) {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, name, role, otp, otp_expires
                 FROM users
                 WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            // Verify OTP and expiry
            if ($user['otp'] !== $otp || strtotime($user['otp_expires']) < time()) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid or expired OTP'
                ];
            }

            // Clear OTP
            $stmt = $this->db->prepare(
                "UPDATE users SET otp = NULL, otp_expires = NULL WHERE id = ?"
            );
            $stmt->execute([$user['id']]);

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['email'] = $email;

            // Return user data (excluding sensitive info)
            return [
                'status' => 'success',
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'email' => $email,
                    'name' => $user['name'],
                    'role' => $user['role']
                ]
            ];

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Verification failed'
            ];
        }
    }
    
    /**
     * Update user profile
     * @param int $userId User ID
     * @param array $data Profile data
     * @return array Response with status and message
     */
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['name'];
            $updates = [];
            $params = [];
            
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updates[] = "$field = ?";
                    $params[] = sanitizeInput($value);
                }
            }
            
            if (empty($updates)) {
                return [
                    'status' => 'error',
                    'message' => 'No valid fields to update'
                ];
            }
            
            $params[] = $userId;
            $stmt = $this->db->prepare(
                "UPDATE users SET " . implode(', ', $updates) . 
                " WHERE id = ?"
            );
            $stmt->execute($params);
            
            return [
                'status' => 'success',
                'message' => 'Profile updated successfully'
            ];
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to update profile'
            ];
        }
    }
    
    /**
     * Check if the current user is an admin
     * @return bool True if user is an admin, false otherwise
     */
    public function isAdmin() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare(
                "SELECT role FROM users WHERE id = ?"
            );
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $user && $user['role'] === 'admin';
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is authenticated
     * @return bool True if authenticated, false otherwise
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get current user data
     * @return array|null User data or null if not authenticated
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare(
                "SELECT id, name, email, role
                 FROM users
                 WHERE id = ?"
            );
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }
    
    /**
     * Register new user with password
     * @param string $name Full name
     * @param string $email Email address
     * @param string $password Password
     * @return array Response with status and user data if successful
     */
    public function register($name, $email, $phone, $password) {
        try {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid email address format'
                ];
            }

            // Validate password length
            if (strlen($password) < 6) {
                return [
                    'status' => 'error',
                    'message' => 'Password must be at least 6 characters long'
                ];
            }

            // Validate phone format (+2547/1XXXXXXXX)
            if (!preg_match('/^\+254[17]\d{8}$/', $phone)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid phone number format'
                ];
            }

            // Check if user already exists (by email or phone)
            $stmt = $this->db->prepare(
                "SELECT id FROM users WHERE email = ? OR phone = ?"
            );
            $stmt->execute([$email, $phone]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                return [
                    'status' => 'error',
                    'message' => 'Email address or phone number already registered'
                ];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Create new user
            $stmt = $this->db->prepare(
                "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'citizen')"
            );
            $stmt->execute([$name, $email, $phone, $hashedPassword]);
            $userId = $this->db->lastInsertId();

            // Set session
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_role'] = 'citizen';
            $_SESSION['email'] = $email;

            // Return user data
            return [
                'status' => 'success',
                'message' => 'Account created successfully',
                'user' => [
                    'id' => $userId,
                    'email' => $email,
                    'role' => 'citizen'
                ]
            ];

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Registration failed'
            ];
        }
    }

    /**
     * Login user with password
     * @param string $email Email address
     * @param string $password Password
     * @return array Response with status and user data if successful
     */
    public function login($email, $password) {
        try {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid email address format'
                ];
            }

            // Check if user exists
            $stmt = $this->db->prepare(
                "SELECT id, name, email, password, role FROM users WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'Email address not found'
                ];
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid password'
                ];
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['email'] = $email;

            // Return user data
            return [
                'status' => 'success',
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'email' => $email,
                    'name' => $user['name'],
                    'role' => $user['role']
                ]
            ];

        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Login failed'
            ];
        }
    }

    /**
     * Logout user
     * @return void
     */
    public function logout() {
        session_destroy();
    }
}
