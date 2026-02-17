<?php
// Simple test to check if basic PHP functionality is working
header('Content-Type: application/json');

echo json_encode(['success' => true, 'message' => 'Simple test working!']);
?>