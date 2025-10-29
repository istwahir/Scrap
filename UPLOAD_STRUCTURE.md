# Upload Directory Structure

This document describes the organized upload folder structure for the Kiambu Recycling & Scraps system.

## Directory Organization

```
public/uploads/
├── .htaccess                 # Main upload security config
├── requests/                 # Collection request photos
│   ├── .htaccess            # Images only
│   └── [user uploaded images]
├── proofs/                   # Collection completion proofs
│   ├── .htaccess            # Images only
│   └── [collector proof photos]
├── collectors/               # Collector registration documents
│   ├── .htaccess            # Images & PDFs
│   └── [ID cards, vehicle docs, certificates]
├── rewards/                  # Reward item images
│   ├── .htaccess            # Images only
│   └── [reward product images]
├── profiles/                 # User profile pictures
│   ├── .htaccess            # Images only
│   └── [profile avatars]
├── dropoffs/                 # Drop-off point images
│   ├── .htaccess            # Images only
│   └── [drop-off location photos]
└── admin/                    # Admin uploads
    ├── .htaccess            # Images, PDFs, CSV, Excel
    └── [reports, documents]
```

## Upload Categories

### 1. **Requests** (`/uploads/requests/`)
- **Purpose**: Photos of waste materials submitted by citizens with collection requests
- **Allowed Types**: JPEG, PNG, GIF, WebP
- **Max Size**: 5MB
- **Used By**: Citizens creating collection requests
- **API**: `/api/create_request.php`

### 2. **Proofs** (`/uploads/proofs/`)
- **Purpose**: Photo evidence of completed collections
- **Allowed Types**: JPEG, PNG, GIF, WebP
- **Max Size**: 5MB
- **Used By**: Collectors marking requests as complete
- **API**: `/api/collectors/complete_collection.php`

### 3. **Collectors** (`/uploads/collectors/`)
- **Purpose**: Registration documents for collector verification
- **Allowed Types**: JPEG, PNG, GIF, PDF
- **Max Size**: 5MB per file
- **Documents**:
  - ID Card (front & back)
  - Vehicle Registration
  - Good Conduct Certificate
- **Used By**: Collectors during registration
- **API**: `/api/collectors/register.php`

### 4. **Rewards** (`/uploads/rewards/`)
- **Purpose**: Product images for reward catalog
- **Allowed Types**: JPEG, PNG, GIF, WebP
- **Max Size**: 3MB
- **Used By**: Admins managing reward items
- **API**: `/api/admin/rewards.php`

### 5. **Profiles** (`/uploads/profiles/`)
- **Purpose**: User profile pictures/avatars
- **Allowed Types**: JPEG, PNG, GIF, WebP
- **Max Size**: 2MB
- **Used By**: All users (citizens, collectors, admins)
- **API**: `/api/update_profile.php`

### 6. **Dropoffs** (`/uploads/dropoffs/`)
- **Purpose**: Images of drop-off point locations
- **Allowed Types**: JPEG, PNG, GIF, WebP
- **Max Size**: 3MB
- **Used By**: Admins managing drop-off points
- **API**: `/api/admin/dropoffs.php`

### 7. **Admin** (`/uploads/admin/`)
- **Purpose**: Administrative documents and reports
- **Allowed Types**: JPEG, PNG, GIF, PDF, CSV, Excel
- **Max Size**: 10MB
- **Used By**: Admins for system documentation
- **API**: Various admin APIs

## Usage - UploadHelper Class

### Location
`/includes/upload_config.php`

### Basic Upload Example

```php
<?php
require_once __DIR__ . '/../includes/upload_config.php';

try {
    // Upload a file
    $result = UploadHelper::uploadFile($_FILES['photo'], 'requests');
    
    // Result contains:
    // - filename: Generated filename
    // - full_path: Absolute server path
    // - relative_path: Path to store in database
    // - url: Public URL to access file
    // - size: File size in bytes
    // - category: Upload category
    
    $photoPath = $result['relative_path'];
    // Store $photoPath in database
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
```

### Validation Only

```php
<?php
// Just validate without uploading
try {
    UploadHelper::validateFile($_FILES['photo'], 'requests');
    echo "File is valid!";
} catch (Exception $e) {
    echo "Validation error: " . $e->getMessage();
}
?>
```

### Delete File

```php
<?php
// Delete a file by its relative path
$relativePath = 'public/uploads/requests/12345.jpg';
$deleted = UploadHelper::deleteFile($relativePath);
?>
```

### Get Upload Statistics

```php
<?php
// Get stats for all categories
$allStats = UploadHelper::getUploadStats();

// Get stats for specific category
$requestStats = UploadHelper::getUploadStats('requests');
/*
Returns:
[
    'count' => 150,           // Number of files
    'total_size' => 52428800, // Bytes
    'total_size_mb' => 50.0   // Megabytes
]
*/
?>
```

### Get Directory Info

```php
<?php
$dirInfo = UploadHelper::getUploadDir('requests');
/*
Returns:
[
    'full_path' => '/var/www/html/Scrap/public/uploads/requests/',
    'relative_path' => 'public/uploads/requests/',
    'url_path' => '/Scrap/public/uploads/requests/',
    'config' => [
        'path' => 'requests/',
        'description' => '...',
        'allowed_types' => [...],
        'max_size' => 5242880,
        'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ]
]
*/
?>
```

## Security Features

### Per-Directory Protection
Each upload subdirectory has its own `.htaccess` file that:
- Disables PHP execution
- Restricts file types based on category
- Prevents directory browsing
- Blocks direct access to sensitive files

### File Validation
The UploadHelper class performs:
- MIME type verification (not just extension checking)
- File size validation
- Allowed file type enforcement
- Secure filename generation

### Naming Convention
Files are named using:
```
upload_{unique_id}_{timestamp}.{extension}
```
This prevents:
- Name collisions
- Path traversal attacks
- Predictable filenames

## Database Storage

When storing file paths in the database, always use the **relative path**:

```sql
-- Good ✓
photo_url = 'public/uploads/requests/upload_abc123_1698765432.jpg'

-- Bad ✗
photo_url = '/Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads/requests/...'
photo_url = 'http://localhost/Scrap/public/uploads/requests/...'
```

## Migrating Existing Uploads

If you have files in the old `/public/uploads/` root directory:

```bash
# Example: Move existing request photos
cd /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads
mv request_*.jpg requests/

# Update database paths
UPDATE requests 
SET photo_url = REPLACE(photo_url, 'public/uploads/', 'public/uploads/requests/')
WHERE photo_url LIKE 'public/uploads/%' 
AND photo_url NOT LIKE 'public/uploads/requests/%';
```

## Backup Strategy

Recommended backup structure:
```bash
backups/
└── uploads/
    └── 2025-10-29/
        ├── requests/
        ├── proofs/
        ├── collectors/
        └── [other categories]
```

## Maintenance

### Check Upload Sizes
```bash
# Check size of each category
du -sh /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads/*
```

### Clean Old Files
Create a cleanup script for temporary or expired uploads:
```php
<?php
// Clean files older than 30 days in specific categories
$categories = ['proofs', 'requests'];
foreach ($categories as $category) {
    $dirInfo = UploadHelper::getUploadDir($category);
    $files = glob($dirInfo['full_path'] . '*');
    
    foreach ($files as $file) {
        if (is_file($file) && time() - filemtime($file) > 30 * 86400) {
            unlink($file);
        }
    }
}
?>
```

## Troubleshooting

### Permission Issues
```bash
# Set correct permissions
chmod 755 /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads
chmod 755 /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads/*
chmod 644 /Applications/XAMPP/xamppfiles/htdocs/Scrap/public/uploads/*/.htaccess
```

### Upload Limit
Check PHP settings:
```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 128M
```

### 403 Forbidden Errors
- Check `.htaccess` file in category folder
- Verify file extension is allowed
- Check Apache configuration for Allow/Deny directives

## Best Practices

1. **Always use UploadHelper** - Don't write custom upload logic
2. **Store relative paths** - Not absolute paths or URLs in database
3. **Validate on upload** - Don't trust client-side validation
4. **Clean up on delete** - Remove files when deleting database records
5. **Use appropriate categories** - Don't dump everything in one folder
6. **Monitor disk space** - Set up alerts for upload directory size
7. **Regular backups** - Back up upload directories separately
8. **Test permissions** - Verify write access before deployment

---

**Last Updated**: October 29, 2025
**Version**: 1.0
