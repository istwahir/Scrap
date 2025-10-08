<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\SecureServer;
use React\Socket\Server;

class NotificationServer implements \Ratchet\MessageComponentInterface {
    protected $clients;
    protected $userConnections;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
    }
    
    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }
    
    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if ($data && isset($data['type'])) {
            switch ($data['type']) {
                case 'auth':
                    if (isset($data['user_id'])) {
                        $this->userConnections[$data['user_id']] = $from;
                        echo "User {$data['user_id']} authenticated\n";
                    }
                    break;
                    
                case 'location_update':
                    if (isset($data['collector_id'], $data['lat'], $data['lng'])) {
                        $this->broadcastCollectorLocation($data);
                    }
                    break;
                    
                case 'request_status':
                    if (isset($data['request_id'], $data['status'])) {
                        $this->broadcastRequestUpdate($data);
                    }
                    break;
            }
        }
    }
    
    public function onClose(\Ratchet\ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        // Remove from user connections
        foreach ($this->userConnections as $userId => $connection) {
            if ($connection === $conn) {
                unset($this->userConnections[$userId]);
                break;
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    protected function broadcastCollectorLocation($data) {
        try {
            $db = getDBConnection();
            
            // Get current requests for this collector
            $stmt = $db->prepare(
                "SELECT user_id 
                 FROM collection_requests 
                 WHERE collector_id = ? 
                 AND status IN ('assigned', 'en_route')"
            );
            $stmt->execute([$data['collector_id']]);
            
            // Notify users waiting for this collector
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (isset($this->userConnections[$row['user_id']])) {
                    $this->userConnections[$row['user_id']]->send(json_encode([
                        'type' => 'collector_location',
                        'collector_id' => $data['collector_id'],
                        'lat' => $data['lat'],
                        'lng' => $data['lng']
                    ]));
                }
            }
            
        } catch (Exception $e) {
            error_log("Error broadcasting collector location: " . $e->getMessage());
        }
    }
    
    protected function broadcastRequestUpdate($data) {
        try {
            $db = getDBConnection();
            
            // Get request details
            $stmt = $db->prepare(
                "SELECT user_id, collector_id 
                 FROM collection_requests 
                 WHERE id = ?"
            );
            $stmt->execute([$data['request_id']]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($request) {
                // Notify user
                if (isset($this->userConnections[$request['user_id']])) {
                    $this->userConnections[$request['user_id']]->send(json_encode([
                        'type' => 'request_update',
                        'request_id' => $data['request_id'],
                        'status' => $data['status']
                    ]));
                }
                
                // Notify collector
                if ($request['collector_id'] && isset($this->userConnections[$request['collector_id']])) {
                    $this->userConnections[$request['collector_id']]->send(json_encode([
                        'type' => 'request_update',
                        'request_id' => $data['request_id'],
                        'status' => $data['status']
                    ]));
                }
            }
            
        } catch (Exception $e) {
            error_log("Error broadcasting request update: " . $e->getMessage());
        }
    }
}

// Create event loop and socket server
$loop = Factory::create();
$socket = new Server('0.0.0.0:8080', $loop);

// Setup WebSocket server
$server = new IoServer(
    new HttpServer(
        new WsServer(
            new NotificationServer()
        )
    ),
    $socket,
    $loop
);

echo "WebSocket server running on ws://0.0.0.0:8080\n";
$server->run();