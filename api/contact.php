<?php
/**
 * Contact Form Handler
 * Processes contact form submissions and sends emails
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Load dependencies
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/email_config.php';
require_once __DIR__ . '/../config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$requiredFields = ['name', 'email', 'subject', 'message'];
$errors = [];

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        $errors[] = ucfirst($field) . ' is required';
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
    exit;
}

// Validate email format
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Sanitize inputs
$name = htmlspecialchars(trim($data['name']));
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars(trim($data['subject']));
$message = htmlspecialchars(trim($data['message']));

try {
    // Save to database
    $messageId = saveContactMessage($name, $email, $subject, $message);
    
    if (!$messageId) {
        throw new Exception('Failed to save message to database');
    }
    
    // Send notification to admin
    $adminMailSent = sendContactNotification($name, $email, $subject, $message);
    
    // Send confirmation to user
    $userMailSent = sendContactConfirmation($name, $email, $subject);
    
    if ($adminMailSent && $userMailSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your message! We will get back to you soon.'
        ]);
    } else {
        throw new Exception('Failed to send emails');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send message. Please try again later.',
        'error' => $e->getMessage()
    ]);
}

/**
 * Send contact notification to admin
 */
function sendContactNotification($name, $email, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress(ADMIN_EMAIL, 'WBS 2026 Admin');
        $mail->addReplyTo($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Contact Form: $subject";
        $mail->Body = getContactNotificationTemplate($name, $email, $subject, $message);
        $mail->AltBody = strip_tags($mail->Body);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Contact Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send confirmation to user
 */
function sendContactConfirmation($name, $email, $subject) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email, $name);
        $mail->addReplyTo(REPLY_TO_EMAIL, FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'We Received Your Message - WBS 2026';
        $mail->Body = getContactConfirmationTemplate($name, $subject);
        $mail->AltBody = strip_tags($mail->Body);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Confirmation Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Contact notification template for admin
 */
function getContactNotificationTemplate($name, $email, $subject, $message) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #1a237e; color: white; padding: 20px; }
            .content { background: #f9f9f9; padding: 20px; }
            .message-box { background: white; padding: 20px; border-left: 4px solid #ff6b35; margin: 20px 0; }
            table { width: 100%; margin: 20px 0; }
            th, td { padding: 10px; text-align: left; }
            th { background: #f0f0f0; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Contact Form Submission</h2>
            </div>
            <div class='content'>
                <p><strong>You have received a new message from WBS 2026 website:</strong></p>
                
                <table>
                    <tr><th>Name:</th><td>$name</td></tr>
                    <tr><th>Email:</th><td>$email</td></tr>
                    <tr><th>Subject:</th><td>$subject</td></tr>
                    <tr><th>Time:</th><td>" . date('F j, Y, g:i a') . "</td></tr>
                </table>
                
                <div class='message-box'>
                    <strong>Message:</strong><br><br>
                    " . nl2br($message) . "
                </div>
                
                <p><em>Reply to this email to respond directly to the sender.</em></p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Contact confirmation template for user
 */
function getContactConfirmationTemplate($name, $subject) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #1a237e 0%, #283593 100%); color: white; padding: 30px; text-align: center; }
            .content { background: #f9f9f9; padding: 30px; }
            .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; }
            .info-box { background: white; padding: 15px; border-left: 4px solid #ff6b35; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>WBS 2026</h1>
                <p>World Branding and Signage Summit</p>
            </div>
            <div class='content'>
                <h2>Message Received!</h2>
                <p>Dear $name,</p>
                <p>Thank you for contacting us! We have received your message regarding \"$subject\".</p>
                
                <div class='info-box'>
                    <strong>What happens next?</strong><br>
                    Our team will review your message and get back to you within 24-48 hours.
                </div>
                
                <p>If your inquiry is urgent, please feel free to call us or send an email directly to <a href='mailto:info@wbssummit.com.ng'>info@wbssummit.com.ng</a></p>
                
                <p>In the meantime, you can:</p>
                <ul>
                    <li>Learn more about WBS 2026 on our website</li>
                    <li>Register for the summit if you haven't already</li>
                    <li>Follow us on social media for updates</li>
                </ul>
                
                <p>Best regards,<br>
                <strong>WBS 2026 Team</strong><br>
                Worklink Consulting</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Save contact message to database
 */
function saveContactMessage($name, $email, $subject, $message) {
    $db = getDBConnection();
    
    if (!$db) {
        error_log("Failed to get database connection");
        return false;
    }
    
    $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $db->error);
        $db->close();
        return false;
    }
    
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    
    $success = $stmt->execute();
    $insertId = $success ? $stmt->insert_id : false;
    
    $stmt->close();
    $db->close();
    
    return $insertId;
}
