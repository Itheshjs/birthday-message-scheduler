<?php
// Simple scheduler test
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Return a simple test response
    echo json_encode(['success' => true, 'data' => [], 'message' => 'GET request working']);
} elseif ($method === 'POST') {
    // Handle POST request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data) {
        echo json_encode(['success' => true, 'message' => 'POST request received', 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>