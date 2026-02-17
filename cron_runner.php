<?php
// This script runs the scheduled message checker
// It should be called by a system cron job every minute

require_once 'send_email.php';

// Log execution
$logFile = __DIR__ . '/scheduler.log';
$message = "Cron executed at: " . date('Y-m-d H:i:s') . "\n";
file_put_contents($logFile, $message, FILE_APPEND);
error_log($message);
echo $message;

// Process scheduled messages
sendScheduledMessages();
echo "Done processing.\n";

// Optional: Also handle SMS sending if phone numbers are provided
// This would require an SMS gateway service
function sendSMSNotifications() {
    // Placeholder for SMS functionality
    // Would integrate with services like Twilio, Plivo, etc.
    /*
    $db = getMongoConnection();
    $collection = $db->selectCollection('scheduled_messages');
    
    // Get unsent messages that include phone numbers
    $now = new DateTime();
    
    // Find messages where scheduled_datetime is less than or equal to now, sent is false, and phone is not null
    $cursor = $collection->find([
        'sent' => false,
        'phone' => ['$ne' => null, '$exists' => true, '$ne' => ''],
        'scheduled_datetime' => ['$lte' => $now]
    ]);
    
    $messages = iterator_to_array($cursor);
    
    foreach ($messages as $message) {
        // Send SMS using an SMS gateway
        // This is a placeholder - actual implementation would depend on the SMS provider
        $smsResult = sendSMS($message['phone'], $message['message']);
        
        if ($smsResult) {
            // Mark as sent in database
            $result = $collection->updateOne(
                ['_id' => $message['_id']],
                ['$set' => ['sent' => true, 'sent_at' => new DateTime()]]
            );
            
            error_log("SMS sent successfully to {$message['phone']} (ID: " . (string)$message['_id'] . ")");
        }
    }
    */
}

// Execute SMS notifications as well
// sendSMSNotifications();
?>