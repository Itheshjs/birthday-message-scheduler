<?php
// Enable error reporting for debugging but suppress display
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Start output buffering to capture any accidental output
ob_start();

$method = $_SERVER['REQUEST_METHOD'];

// Only execute the rest if this file is called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
    initializeDatabase();
} catch (Exception $e) {
    // Clear any accidental output
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Database initialization failed: ' . $e->getMessage()]);
    exit;
}

try {
    $db = getMongoConnection();
    if (!$db) {
        throw new Exception('Failed to connect to database');
    }
    $collection = $db->selectCollection('scheduled_messages');
    if (!$collection) {
        throw new Exception('Failed to get collection');
    }
    
    switch ($method) {
        case 'POST':
            // Schedule a new message
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Combine date and time into a single datetime
            try {
                $scheduled_datetime = new DateTime($data['date'] . ' ' . $data['time']);
            } catch (Exception $e) {
                // Clear any accidental output
                ob_clean();
                http_response_code(400);
                echo json_encode(['error' => 'Invalid date/time format: ' . $e->getMessage()]);
                exit;
            }
            
            $document = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => isset($data['phone']) ? $data['phone'] : null,
                'scheduled_datetime' => $scheduled_datetime,
                'message' => $data['message'],
                'created_at' => new DateTime(),
                'sent' => false
            ];
            
            // Validate required fields
            if (empty($data['name']) || empty($data['email']) || empty($data['date']) || empty($data['time']) || empty($data['message'])) {
                // Clear any accidental output
                ob_clean();
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            
            $result = $collection->insertOne($document);
            
            // Check if insertion was successful
            if (isset($result->insertedCount) && $result->insertedCount > 0) {
                // Clear any accidental output
                ob_clean();
                echo json_encode(['success' => true, 'message' => 'Message scheduled successfully']);
            } else {
                // Clear any accidental output
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Failed to schedule message']);
            }
            break;
            
        case 'GET':
            // Get all scheduled messages
            $cursor = $collection->find(
                ['sent' => false],
                ['sort' => ['scheduled_datetime' => 1]]
            );
            $messages = iterator_to_array($cursor);
            
            // Convert MongoDB documents to array format similar to the old one
            $formattedMessages = [];
            foreach ($messages as $msg) {
                // Handle DateTime objects or arrays
                $scheduled_datetime = $msg['scheduled_datetime'];
                $created_at = $msg['created_at'];
                
                // Convert to DateTime if they're arrays (from JSON storage)
                if (is_array($scheduled_datetime)) {
                    $scheduled_datetime = new DateTime($scheduled_datetime['date']);
                }
                if (is_array($created_at)) {
                    $created_at = new DateTime($created_at['date']);
                }
                
                $formattedMessages[] = [
                    'id' => (string)$msg['_id'],
                    'name' => $msg['name'],
                    'email' => $msg['email'],
                    'phone' => $msg['phone'],
                    'scheduled_date' => $scheduled_datetime->format('Y-m-d'),
                    'scheduled_time' => $scheduled_datetime->format('H:i:s'),
                    'message' => $msg['message'],
                    'created_at' => $created_at->format('Y-m-d H:i:s'),
                    'sent' => $msg['sent']
                ];
            }
            
            // Clear any accidental output
            ob_clean();
            echo json_encode(['success' => true, 'data' => $formattedMessages]);
            break;
            
        case 'DELETE':
            // Delete a scheduled message
            $id = $_GET['id'];
            
            // Check if MongoDB extension is available
            if (class_exists('MongoDB\\BSON\\ObjectId')) {
                $objectId = new MongoDB\BSON\ObjectId($id);
            } else {
                // Use our stub ObjectId implementation
                require_once 'MongoDBStub.php';
                $objectId = new ObjectId($id);
            }
            $result = $collection->deleteOne(['_id' => $objectId]);
            
            if ($result->getDeletedCount() > 0) {
                // Clear any accidental output
                ob_clean();
                echo json_encode(['success' => true, 'message' => 'Message deleted successfully']);
            } else {
                // Clear any accidental output
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Failed to delete message']);
            }
            break;
            
        default:
            // Clear any accidental output
            ob_clean();
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    // Clear any accidental output
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // End output buffering
    ob_end_flush();
}
} // End if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']))
?>