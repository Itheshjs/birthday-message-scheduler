<?php
// Simple mail implementation as an alternative to PHPMailer

class SimpleMail {
    public $to = [];
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $isHtml = false;
    private $headers = [];
    
    const ENCRYPTION_STARTTLS = 'tls';

    public function __construct() {
        $this->headers[] = 'MIME-Version: 1.0';
    }
    
    public function isHTML($bool = true) {
        $this->isHtml = $bool;
        if ($bool) {
            $this->headers[] = 'Content-type: text/html; charset=UTF-8';
        } else {
            $this->headers[] = 'Content-type: text/plain; charset=UTF-8';
        }
    }
    
    public function setFrom($address, $name = '') {
        $this->From = $address;
        $this->FromName = $name;
        $from = $name ? "$name <$address>" : $address;
        $this->headers[] = "From: {$from}";
    }
    
    public function addAddress($address, $name = '') {
        $recipient = $name ? "$name <$address>" : $address;
        $this->to[] = $recipient;
    }
    
    public function Subject($subject) {
        $this->subject = $subject;
    }
    
    public function Body($body) {
        $this->body = $body;
    }
    
    public function AltBody($altBody) {
        // For our implementation, we'll ignore alternative body
    }
    
    public function send() {
        $to = implode(', ', $this->to);
        $headers = implode("\r\n", $this->headers);
        
        return mail($to, $this->Subject, $this->Body, $headers);
    }
}

// Create aliases to match PHPMailer usage
// Create aliases to match PHPMailer usage
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    class_alias('SimpleMail', 'PHPMailer\PHPMailer\PHPMailer');
}

// Define constants for PHPMailer compatibility
// Define constants and classes for PHPMailer compatibility
if (!defined('PHPMailer\\PHPMailer\\PHPMailer::ENCRYPTION_STARTTLS')) {
    // We need to define these classes in the global namespace if they don't exist
    // and then alias them if needed, or better, just define the aliases directly if possible.
    // However, PHP class_alias works for existing classes.
    
    // Let's just create a dummy class to alias to
    if (!class_exists('SimpleSMTP')) {
        class SimpleSMTP {}
    }
    if (!class_exists('SimpleException')) {
        class SimpleException extends \Exception {}
    }
    
    class_alias('SimpleSMTP', 'PHPMailer\PHPMailer\SMTP');
    class_alias('SimpleException', 'PHPMailer\PHPMailer\Exception');
}
?>