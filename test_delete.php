<?php
require_once 'config.php';
require_once 'scheduler.php';

// Force timezone
date_default_timezone_set('Asia/Kolkata');

$db = getMongoConnection();
$collection = $db->selectCollection('scheduled_messages');

// 1. Create a dummy message
$testMessage = [
    'name' => 'Delete Me',
    'email' => 'test@example.com',
    'phone' => '0000000000',
    'scheduled_datetime' => new DateTime('+1 hour'),
    'message' => 'To be deleted',
    'created_at' => new DateTime(),
    'sent' => false
];
$collection->insertOne($testMessage);
echo "Created dummy message.\n";

// 2. Find it to get ID
$cursor = $collection->find(['name' => 'Delete Me']);
$id = null;
foreach ($cursor as $doc) {
    if ($doc['name'] === 'Delete Me') {
        $id = (string)$doc['_id'];
        echo "Found dummy message with ID: $id\n";
        break;
    }
}

if ($id) {
    // 3. Delete it using the logic from scheduler.php
    if (class_exists('MongoDB\\BSON\\ObjectId')) {
        $objectId = new MongoDB\BSON\ObjectId($id);
    } else {
        require_once 'MongoDBStub.php';
        $objectId = new ObjectId($id);
    }
    
    echo "Attempting delete...\n";
    $result = $collection->deleteOne(['_id' => $objectId]);
    echo "Deleted count: " . $result->getDeletedCount() . "\n";
    
    if ($result->getDeletedCount() === 1) {
        echo "DELETE VERIFICATION SUCCESS\n";
    } else {
        echo "DELETE VERIFICATION FAILED\n";
    }
} else {
    echo "Could not find dummy message to delete.\n";
}

// 4. Check log file
$logFile = __DIR__ . '/scheduler.log';
if (file_exists($logFile)) {
    echo "Log file exists. Content preview:\n";
    echo substr(file_get_contents($logFile), 0, 200) . "...\n";
} else {
    echo "Log file does not exist yet (run cron_runner.php to create it).\n";
}
?>
