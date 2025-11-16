<?php
header('Content-Type: application/json');
require_once '../../config.php';

$__t0 = microtime(true);
error_log('[get_dropoff_points] start');

$conn = getDBConnection();
error_log('[get_dropoff_points] db ready in ' . number_format((microtime(true)-$__t0)*1000,2) . ' ms');

if (!isset($_SESSION['user_id'])) {
    error_log('[get_dropoff_points] no session user');
    echo json_encode(['status'=>'error','message'=>'Not authenticated','elapsed_ms'=>number_format((microtime(true)-$__t0)*1000,2)]);
    exit;
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'collector') {
    error_log('[get_dropoff_points] invalid role ' . ($_SESSION['user_role'] ?? 'none'));
    echo json_encode(['status'=>'error','message'=>'Access denied','elapsed_ms'=>number_format((microtime(true)-$__t0)*1000,2)]);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    // Release session lock to prevent concurrent request blocking
    session_write_close();
    $prepStart = microtime(true);
    $stmt = $conn->prepare(<<<SQL
        SELECT 
            dp.*,
            COUNT(DISTINCT cr.id) AS collection_count
        FROM dropoff_points dp
        LEFT JOIN collection_requests cr ON dp.id = cr.dropoff_point_id
        WHERE dp.added_by = ? AND dp.added_by_role = 'collector'
        GROUP BY dp.id
        ORDER BY dp.created_at DESC
SQL);
    error_log('[get_dropoff_points] prepared in ' . number_format((microtime(true)-$prepStart)*1000,2) . ' ms');
    $execStart = microtime(true);
    $stmt->execute([$userId]);
    $execEnd = microtime(true);
    error_log('[get_dropoff_points] execute in ' . number_format(($execEnd-$execStart)*1000,2) . ' ms');
    $dropoffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $fetchEnd = microtime(true);
    error_log('[get_dropoff_points] fetchAll in ' . number_format(($fetchEnd-$execEnd)*1000,2) . ' ms total ' . number_format(($fetchEnd-$__t0)*1000,2));

    $formatted = array_map(function($d){
        return [
            'id'=>$d['id'],
            'name'=>$d['name'],
            'address'=>$d['address'],
            'lat'=>$d['lat'],
            'lng'=>$d['lng'],
            'contact_phone'=>$d['contact_phone'],
            'operating_hours'=>$d['operating_hours'],
            'materials'=>explode(',', $d['materials']),
            'photo_url'=>$d['photo_url'],
            'status'=>$d['status'],
            'created_at'=>$d['created_at'],
            'collection_count'=>$d['collection_count']
        ];
    }, $dropoffs);

    echo json_encode([
        'status'=>'success',
        'generated_at'=>date('c'),
        'elapsed_ms'=>number_format((microtime(true)-$__t0)*1000,2),
        'dropoffs'=>$formatted,
        'total'=>count($formatted)
    ]);
    error_log('[get_dropoff_points] done total ' . number_format((microtime(true)-$__t0)*1000,2) . ' ms');
} catch (PDOException $e) {
    error_log('[get_dropoff_points] PDO error ' . $e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Database error occurred','elapsed_ms'=>number_format((microtime(true)-$__t0)*1000,2)]);
} catch (Exception $e) {
    error_log('[get_dropoff_points] general error ' . $e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Server error occurred','elapsed_ms'=>number_format((microtime(true)-$__t0)*1000,2)]);
}
