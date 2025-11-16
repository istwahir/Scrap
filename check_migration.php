<?php
/**
 * Database Migration Checker
 * Run this script to check if dropoff_points table needs migration
 * Access via: http://localhost/Scrap/check_migration.php
 */

require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Database Migration Status</h1>
        
        <?php
        try {
            $conn = getDBConnection();
            
            // Check if photo_url column exists
            $stmt = $conn->query("SHOW COLUMNS FROM dropoff_points LIKE 'photo_url'");
            $photoUrlExists = $stmt->rowCount() > 0;
            
            // Check if suggested_by column exists
            $stmt = $conn->query("SHOW COLUMNS FROM dropoff_points LIKE 'suggested_by'");
            $suggestedByExists = $stmt->rowCount() > 0;
            
            echo '<div class="space-y-4">';
            
            // Photo URL Status
            if ($photoUrlExists) {
                echo '<div class="flex items-center gap-2 text-green-600">';
                echo '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
                echo '<span class="font-semibold">✓ photo_url column exists</span>';
                echo '</div>';
            } else {
                echo '<div class="flex items-center gap-2 text-red-600">';
                echo '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
                echo '<span class="font-semibold">✗ photo_url column missing</span>';
                echo '</div>';
            }
            
            // Suggested By Status
            if ($suggestedByExists) {
                echo '<div class="flex items-center gap-2 text-green-600">';
                echo '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
                echo '<span class="font-semibold">✓ suggested_by column exists</span>';
                echo '</div>';
            } else {
                echo '<div class="flex items-center gap-2 text-red-600">';
                echo '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
                echo '<span class="font-semibold">✗ suggested_by column missing</span>';
                echo '</div>';
            }
            
            echo '</div>';
            
            // Overall Status
            if ($photoUrlExists && $suggestedByExists) {
                echo '<div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">';
                echo '<h2 class="text-lg font-bold text-green-800 mb-2">✓ Migration Complete!</h2>';
                echo '<p class="text-green-700">All required columns exist. Drop-off point suggestions should work correctly.</p>';
                echo '</div>';
            } else {
                echo '<div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">';
                echo '<h2 class="text-lg font-bold text-red-800 mb-2">⚠️ Migration Required</h2>';
                echo '<p class="text-red-700 mb-4">You need to run the database migration to add missing columns.</p>';
                echo '<div class="bg-white p-4 rounded border border-gray-300 mt-4">';
                echo '<h3 class="font-bold mb-2">Quick Fix (phpMyAdmin):</h3>';
                echo '<ol class="list-decimal list-inside space-y-1 text-sm">';
                echo '<li>Open <a href="http://localhost/phpmyadmin" target="_blank" class="text-blue-600 underline">phpMyAdmin</a></li>';
                echo '<li>Select <code class="bg-gray-100 px-1">kiambu_recycling</code> database</li>';
                echo '<li>Click "SQL" tab</li>';
                echo '<li>Paste and run the SQL below</li>';
                echo '</ol>';
                echo '<pre class="mt-3 p-3 bg-gray-100 rounded text-xs overflow-x-auto">';
                echo htmlspecialchars("ALTER TABLE dropoff_points
ADD COLUMN photo_url VARCHAR(255) NULL AFTER materials,
ADD COLUMN suggested_by INT NULL AFTER photo_url,
ADD CONSTRAINT fk_dropoff_suggested_by FOREIGN KEY (suggested_by) REFERENCES collectors(id) ON DELETE SET NULL,
ADD INDEX idx_dropoff_suggested_by (suggested_by);");
                echo '</pre>';
                echo '</div>';
                echo '<p class="mt-4 text-sm text-gray-600">For detailed instructions, see <code>MIGRATION_GUIDE.md</code></p>';
                echo '</div>';
            }
            
            // Table Structure
            echo '<div class="mt-6">';
            echo '<h2 class="text-lg font-bold mb-2">Current Table Structure:</h2>';
            echo '<div class="overflow-x-auto">';
            echo '<table class="min-w-full border border-gray-300 text-sm">';
            echo '<thead class="bg-gray-100">';
            echo '<tr><th class="border px-4 py-2">Column</th><th class="border px-4 py-2">Type</th><th class="border px-4 py-2">Null</th><th class="border px-4 py-2">Key</th></tr>';
            echo '</thead><tbody>';
            
            $stmt = $conn->query("SHOW COLUMNS FROM dropoff_points");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $highlight = in_array($row['Field'], ['photo_url', 'suggested_by']) ? 'bg-yellow-50' : '';
                echo "<tr class='$highlight'>";
                echo "<td class='border px-4 py-2 font-mono'>{$row['Field']}</td>";
                echo "<td class='border px-4 py-2'>{$row['Type']}</td>";
                echo "<td class='border px-4 py-2'>{$row['Null']}</td>";
                echo "<td class='border px-4 py-2'>{$row['Key']}</td>";
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div></div>';
            
        } catch (PDOException $e) {
            echo '<div class="p-4 bg-red-50 border border-red-200 rounded-lg">';
            echo '<h2 class="text-lg font-bold text-red-800 mb-2">Database Error</h2>';
            echo '<p class="text-red-700">' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
        ?>
        
        <div class="mt-6 flex gap-3">
            <a href="views/collectors/profile.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Go to Profile</a>
            <a href="views/admin/dropoffs.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Admin Drop-offs</a>
            <button onclick="location.reload()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Refresh Check</button>
        </div>
    </div>
</body>
</html>
