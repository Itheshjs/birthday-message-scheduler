<?php
require_once 'config.php';
require_once 'send_email.php';

// Insert a message scheduled for the past (1 hour ago)
$db = getMongoConnection();
$collection = $db->selectCollection('scheduled_messages');

$pastTime = new DateTime();
$pastTime->modify('-1 hour');

$document = [
    'name' => 'Catchup Test',
    'email' => 'itheshja6@gmail.com', // Sending to self for verification
    'message' => 'This is a test message scheduled for the past to verify catch-up logic.',
    'scheduled_datetime' => $pastTime,
    'created_at' => new DateTime(),
    'sent' => false
];

$insertResult = $collection->insertOne($document);
// Handle both BSON ObjectId (real MongoDB) and our stub's simple implementation
$id = $document['_id'];
echo "Inserted test message with ID: " . $id . "\n";
echo "Scheduled Time: " . $pastTime->format('Y-m-d H:i:s') . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";

// Now run the sender
echo "Running scheduler...\n";
sendScheduledMessages();
?>
