<?php
/**
 * Upload Configuration
 * Centralized upload settings for the entire system
 */

// Base upload directory
define('UPLOAD_BASE_DIR', __DIR__ . '/../public/uploads/');
define('UPLOAD_BASE_URL', '/Scrap/public/uploads/');

// Upload directories by category
define('UPLOAD_DIRS', [
    'requests' => [
        'path' => 'requests/',
        'description' => 'Collection request photos from citizens',
        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
        'max_size' => 5 * 1024 * 1024, // 5MB
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ],
    'proofs' => [
        'path' => 'proofs/',
        'description' => 'Collection completion proof photos from collectors',
        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
        'max_size' => 5 * 1024 * 1024, // 5MB
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ],
    'collectors' => [
        'path' => 'collectors/',
        'description' => 'Collector registration documents (ID, vehicle docs, certificates)',
        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'],
        'max_size' => 5 * 1024 * 1024, // 5MB
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf']
    ],
    'rewards' => [
        'path' => 'rewards/',
        'description' => 'Reward item images',
        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
        'max_size' => 3 * 1024 * 1024, // 3MB
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ],
    'profiles' => [
        'path' => 'profiles/',
        'description' => 'User profile pictures',
        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
        'max_size' => 2 * 1024 * 1024, // 2MB
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ],
    'dropoffs' => [
        'path' => 'dropoffs/',
        'description' => 'Drop-off point images',
        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
        'max_size' => 3 * 1024 * 1024, // 3MB
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ],
    'admin' => [
        'path' => 'admin/',
        'description' => 'Admin uploads (reports, documents)',
        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'max_size' => 10 * 1024 * 1024, // 10MB
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'csv', 'xlsx']
    ]
]);

/**
 * Upload Helper Class
 */
class UploadHelper {
    
    /**
     * Get upload directory info
     */
    public static function getUploadDir($category) {
        if (!isset(UPLOAD_DIRS[$category])) {
            throw new Exception("Invalid upload category: $category");
        }
        
        $config = UPLOAD_DIRS[$category];
        $fullPath = UPLOAD_BASE_DIR . $config['path'];
        
        return [
            'full_path' => $fullPath,
            'relative_path' => 'public/uploads/' . $config['path'],
            'url_path' => UPLOAD_BASE_URL . $config['path'],
            'config' => $config
        ];
    }
    
    /**
     * Validate uploaded file
     */
    public static function validateFile($file, $category) {
        if (!isset(UPLOAD_DIRS[$category])) {
            throw new Exception("Invalid upload category: $category");
        }
        
        $config = UPLOAD_DIRS[$category];
        
        // Check if file was uploaded
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
            ];
            
            $error = isset($errors[$file['error']]) ? $errors[$file['error']] : 'Unknown upload error';
            throw new Exception($error);
        }
        
        // Validate MIME type
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);
        
        if (!in_array($detectedType, $config['allowed_types'])) {
            throw new Exception("Invalid file type. Allowed types: " . implode(', ', $config['extensions']));
        }
        
        // Validate file size
        if ($file['size'] > $config['max_size']) {
            $maxSizeMB = round($config['max_size'] / (1024 * 1024), 1);
            throw new Exception("File too large. Maximum size: {$maxSizeMB}MB");
        }
        
        return true;
    }
    
    /**
     * Upload file to appropriate directory
     */
    public static function uploadFile($file, $category, $customName = null) {
        // Validate file
        self::validateFile($file, $category);
        
        // Get upload directory
        $dirInfo = self::getUploadDir($category);
        $config = $dirInfo['config'];
        
        // Create directory if it doesn't exist
        if (!is_dir($dirInfo['full_path'])) {
            if (!mkdir($dirInfo['full_path'], 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }
        
        // Check if directory is writable
        if (!is_writable($dirInfo['full_path'])) {
            throw new Exception('Upload directory is not writable');
        }
        
        // Generate filename
        if ($customName) {
            $fileName = $customName;
        } else {
            // Get MIME type to determine extension
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $file['tmp_name']);
            finfo_close($fileInfo);
            
            $mimeToExt = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf',
                'text/csv' => 'csv',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'
            ];
            
            $extension = $mimeToExt[$mimeType] ?? 'jpg';
            $fileName = uniqid('upload_', true) . '_' . time() . '.' . $extension;
        }
        
        // Move file
        $fullPath = $dirInfo['full_path'] . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Return file info
        return [
            'filename' => $fileName,
            'full_path' => $fullPath,
            'relative_path' => $dirInfo['relative_path'] . $fileName,
            'url' => $dirInfo['url_path'] . $fileName,
            'size' => $file['size'],
            'category' => $category
        ];
    }
    
    /**
     * Delete uploaded file
     */
    public static function deleteFile($relativePath) {
        $fullPath = __DIR__ . '/../' . $relativePath;
        
        if (file_exists($fullPath)) {
            if (!unlink($fullPath)) {
                throw new Exception('Failed to delete file');
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Get upload statistics
     */
    public static function getUploadStats($category = null) {
        $stats = [];
        
        $categories = $category ? [$category] : array_keys(UPLOAD_DIRS);
        
        foreach ($categories as $cat) {
            $dirInfo = self::getUploadDir($cat);
            $path = $dirInfo['full_path'];
            
            if (is_dir($path)) {
                $files = glob($path . '*');
                $totalSize = 0;
                
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $totalSize += filesize($file);
                    }
                }
                
                $stats[$cat] = [
                    'count' => count($files),
                    'total_size' => $totalSize,
                    'total_size_mb' => round($totalSize / (1024 * 1024), 2)
                ];
            } else {
                $stats[$cat] = [
                    'count' => 0,
                    'total_size' => 0,
                    'total_size_mb' => 0
                ];
            }
        }
        
        return $category ? $stats[$category] : $stats;
    }
}
