<?php
// Set default timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

// Database configuration
define('MONGO_HOST', 'localhost');
define('MONGO_PORT', '27017');
define('MONGO_DB', 'birthday_scheduler');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com'); // Change this to your SMTP server
define('SMTP_PORT', 587); // Or 465 for SSL
define('SMTP_USER', 'itheshja6@gmail.com'); // Your email address
define('SMTP_PASS', 'ejqq dcxw yzku hmvd'); // Your email app password

// Create MongoDB connection (with fallback to file-based storage)
function getMongoConnection() {
    error_log('getMongoConnection called');
    // Check if MongoDB extension is available
    if (class_exists('MongoDB\\Client')) {
        try {
            $mongo = new MongoDB\Client("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
            $db = $mongo->selectDatabase(MONGO_DB);
            return $db;
        } catch(Exception $e) {
            // If MongoDB connection fails, fall back to file-based storage
            require_once 'MongoDBStub.php';
            $mongo = new Client("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
            $db = $mongo->selectDatabase(MONGO_DB);
            return $db;
        }
    } else {
        // If MongoDB extension doesn't exist, use file-based storage
        require_once 'MongoDBStub.php';
        $mongo = new Client("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
        $db = $mongo->selectDatabase(MONGO_DB);
        return $db;
    }
}

// Initialize collections if they don't exist
function initializeDatabase() {
    error_log('initializeDatabase called');
    $db = getMongoConnection();
    if (!$db) {
        error_log('Failed to get database connection');
        throw new Exception('Failed to get database connection');
    }
    error_log('Database connection successful');
    
    // Create scheduled_messages collection if it doesn't exist
    // MongoDB creates collections automatically when first document is inserted
    // But we'll ensure indexes are set up
    $collection = $db->selectCollection('scheduled_messages');
    
    // Create indexes for efficient querying
    $collection->createIndex(['scheduled_datetime' => 1]);
    $collection->createIndex(['sent' => 1]);
    $collection->createIndex(['sent' => 1, 'scheduled_datetime' => 1]);
}
?>