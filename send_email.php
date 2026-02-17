<?php
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Try to include PHPMailer if available, otherwise use SimpleMail
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    require_once 'SimpleMail.php';
}
function sendEmail($to, $subject, $body, $name = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Check if we're using the real PHPMailer or our SimpleMail wrapper
        if (method_exists($mail, 'isSMTP')) {
            // Real PHPMailer implementation - try to configure SMTP
            try {
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = SMTP_PORT;
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            } catch (Error $e) {
                // If SMTP setup fails, fall back to PHP mail()
                // Just continue without SMTP
            }
        }
        
        // Recipients
        $mail->setFrom(SMTP_USER, 'Birthday Message Scheduler');
        $mail->addAddress($to, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Function to send scheduled messages
function sendScheduledMessages() {
    $db = getMongoConnection();
    $collection = $db->selectCollection('scheduled_messages');
    
    // Get messages that are due to be sent (not yet sent and time has come)
    $now = new DateTime();
    
    // Find messages where scheduled_datetime is less than or equal to now and sent is false
    $cursor = $collection->find([
        'sent' => false,
        'scheduled_datetime' => ['$lte' => $now]
    ]);
    
    $messages = iterator_to_array($cursor);
    
    foreach ($messages as $message) {
        // Prepare email content
        $subject = "Scheduled Message: " . substr($message['message'], 0, 30) . "...";
        $body = "
        <html>
        <head>
            <title>Scheduled Message</title>
        </head>
        <body>
            <h2>Special Message for You!</h2>
            <p>Hello <strong>{$message['name']}</strong>,</p>
            <p>{$message['message']}</p>
            <br>
            <p>Sent on: " . date('F j, Y \a\t g:i A') . "</p>
        </body>
        </html>
        ";
        
        // Send email
        if (sendEmail($message['email'], $subject, $body, $message['name'])) {
            // Mark as sent in database
            $result = $collection->updateOne(
                ['_id' => $message['_id']],
                ['$set' => ['sent' => true, 'sent_at' => new DateTime()]]
            );
            
            error_log("Message sent successfully to {$message['email']} (ID: " . (string)$message['_id'] . ")");
        } else {
            error_log("Failed to send message to {$message['email']} (ID: " . (string)$message['_id'] . ")");
        }
    }
}

// Run the function when this script is called
/*
// Run the function when this script is called directly or via cron
$is_cli = (php_sapi_name() === 'cli');
$is_direct_call = false;

if ($is_cli) {
    $script_filename = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
    if (!empty($script_filename) && basename(__FILE__) == basename($script_filename)) {
        $is_direct_call = true;
    }
}

if ($is_direct_call || isset($_GET['run'])) {
    sendScheduledMessages();
    echo "Scheduled messages processing completed.\n";
}
*/
?>