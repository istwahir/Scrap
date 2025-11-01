<?php
// Upload helper: manages category-based upload folders and file validation
// Usage: require_once __DIR__ . '/upload_config.php';
// $res = handle_upload('photo', 'requests');
// if (isset($res['error'])) { /* handle error */ } else { $relativePath = $res['path']; }

defined('UPLOAD_CATEGORIES') || define('UPLOAD_CATEGORIES', [
    'requests', 'collectors', 'rewards', 'profiles', 'proofs', 'dropoffs', 'admin', 'temp'
]);

function get_upload_base()
{
    return realpath(__DIR__ . '/../public') . '/uploads/';
}

function ensure_upload_subdir($category)
{
    $base = get_upload_base();
    if (!in_array($category, UPLOAD_CATEGORIES)) {
        $category = 'temp';
    }

    // Use year/month/day structure for easier cleanup
    $datePath = date('Y') . '/' . date('m') . '/' . date('d') . '/';
    $dir = $base . rtrim($category, '/') . '/' . $datePath;

    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            return ['error' => 'Failed to create upload directory', 'path' => $dir];
        }
    }

    // Try to make writable
    if (!is_writable($dir)) {
        @chmod($dir, 0755);
        if (!is_writable($dir)) {
            return ['error' => 'Upload directory is not writable', 'path' => $dir];
        }
    }

    $relative = 'public/uploads/' . rtrim($category, '/') . '/' . $datePath;
    return ['path' => $dir, 'relative' => $relative];
}

function handle_upload($fileField, $category = 'temp', $allowedTypes = [], $maxSize = 5242880)
{
    if (!isset($_FILES[$fileField]) || !is_array($_FILES[$fileField])) {
        return ['error' => 'No file uploaded'];
    }

    if ($_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload error', 'code' => $_FILES[$fileField]['error']];
    }

    $subdirRes = ensure_upload_subdir($category);
    if (isset($subdirRes['error'])) {
        return $subdirRes;
    }

    $dir = $subdirRes['path'];
    $relative = $subdirRes['relative'];

    // Detect MIME type securely
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detected = finfo_file($finfo, $_FILES[$fileField]['tmp_name']);
    finfo_close($finfo);

    $defaults = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (empty($allowedTypes)) $allowedTypes = $defaults;

    if (!in_array($detected, $allowedTypes)) {
        return ['error' => 'Invalid file type'];
    }

    if ($_FILES[$fileField]['size'] > $maxSize) {
        return ['error' => 'File size too large'];
    }

    $extMap = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];

    $ext = $extMap[$detected] ?? pathinfo($_FILES[$fileField]['name'], PATHINFO_EXTENSION);

    try {
        $random = bin2hex(random_bytes(8));
    } catch (Exception $e) {
        $random = uniqid();
    }

    $fileName = $random . '_' . time() . '.' . $ext;
    $fullPath = $dir . $fileName;

    if (!@move_uploaded_file($_FILES[$fileField]['tmp_name'], $fullPath)) {
        return ['error' => 'Failed to move uploaded file', 'path' => $fullPath];
    }

    // Secure file permissions
    @chmod($fullPath, 0644);

    return ['path' => $relative . $fileName, 'full' => $fullPath];
}

?>