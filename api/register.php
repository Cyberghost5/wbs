<?php
/**
 * Registration Form Handler
 * Processes registration submissions and sends confirmation emails
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
$requiredFields = ['firstName', 'lastName', 'email', 'phone', 'country', 'organization', 'position', 'delegateType'];
$errors = [];

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
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
$firstName = htmlspecialchars(trim($data['firstName']));
$lastName = htmlspecialchars(trim($data['lastName']));
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars(trim($data['phone']));
$country = htmlspecialchars(trim($data['country']));
$organization = htmlspecialchars(trim($data['organization']));
$position = htmlspecialchars(trim($data['position']));
$delegateType = htmlspecialchars(trim($data['delegateType']));
$hotelChoice = !empty($data['hotelChoice']) ? htmlspecialchars(trim($data['hotelChoice'])) : 'N/A';
$dietary = !empty($data['dietary']) ? htmlspecialchars(trim($data['dietary'])) : 'None';
$expectations = !empty($data['expectations']) ? htmlspecialchars(trim($data['expectations'])) : 'Not provided';
$newsletter = !empty($data['newsletter']) ? 1 : 0;

try {
    // Save to database
    $registrationId = saveRegistration([
        'firstName' => $firstName,
        'lastName' => $lastName,
        'email' => $email,
        'phone' => $phone,
        'country' => $country,
        'organization' => $organization,
        'position' => $position,
        'delegateType' => $delegateType,
        'hotelChoice' => $hotelChoice,
        'dietary' => $dietary,
        'expectations' => $expectations,
        'newsletter' => $newsletter
    ]);
    
    if (!$registrationId) {
        throw new Exception('Failed to save registration to database');
    }
    
    // Send confirmation email to user
    $userMailSent = sendUserConfirmation($email, $firstName, $lastName, $delegateType);
    
    // Send notification to admin
    $adminMailSent = sendAdminNotification($data);
    
    if ($userMailSent && $adminMailSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Please check your email for confirmation and payment instructions.'
        ]);
    } else {
        throw new Exception('Failed to send confirmation emails');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again later.',
        'error' => $e->getMessage()
    ]);
}

/**
 * Send confirmation email to user
 */
function sendUserConfirmation($email, $firstName, $lastName, $delegateType) {
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
        $mail->addAddress($email, "$firstName $lastName");
        $mail->addReplyTo(REPLY_TO_EMAIL, FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Registration Confirmation - WBS 2026';
        
        $delegateInfo = $delegateType === 'local' 
            ? 'Local Delegate - ₦40,000 ($30 USD)' 
            : 'Foreign Delegate - $300 USD';
        
        $mail->Body = getConfirmationEmailTemplate($firstName, $lastName, $delegateType, $delegateInfo);
        $mail->AltBody = strip_tags($mail->Body);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send notification email to admin
 */
function sendAdminNotification($data) {
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
        $mail->addReplyTo($data['email'], "{$data['firstName']} {$data['lastName']}");
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Registration - WBS 2026';
        $mail->Body = getAdminNotificationTemplate($data);
        $mail->AltBody = strip_tags($mail->Body);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Admin Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Confirmation email template for users
 */
function getConfirmationEmailTemplate($firstName, $lastName, $delegateType, $delegateInfo) {
    $paymentInstructions = $delegateType === 'local'
        ? '<p><strong>Payment Instructions (Local Delegate):</strong><br>
           Amount: ₦40,000 ($30 USD)<br>
           Bank: Access Bank<br>
           Account Number: 1931500038<br>
           Account Name: Workerlink Consulting<br><br>
           Please send your payment confirmation to: info@wbssummit.com.ng</p>'
        : '<p><strong>Payment Instructions (Foreign Delegate):</strong><br>
           Amount: $300 USD<br>
           We will send detailed payment instructions and an official invitation letter within 24 hours.<br><br>
           For assistance, contact: info@wbssummit.com.ng</p>';
    
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
            .button { background: #ff6b35; color: white; padding: 12px 30px; text-decoration: none; display: inline-block; border-radius: 5px; margin: 20px 0; }
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
                <h2>Registration Confirmed!</h2>
                <p>Dear $firstName $lastName,</p>
                <p>Thank you for registering for the World Branding and Signage Summit 2026!</p>
                
                <div class='info-box'>
                    <strong>Registration Details:</strong><br>
                    Name: $firstName $lastName<br>
                    Registration Type: $delegateInfo<br>
                    Event Date: April 24, 2026<br>
                    Location: Nigeria, Africa
                </div>
                
                $paymentInstructions
                
                <p><strong>What's Next?</strong></p>
                <ul>
                    <li>Complete your payment using the instructions above</li>
                    <li>You will receive a confirmation email once payment is verified</li>
                    <li>Event details and agenda will be sent closer to the date</li>
                </ul>
                
                <p>If you have any questions, please don't hesitate to contact us at <a href='mailto:info@wbssummit.com.ng'>info@wbssummit.com.ng</a></p>
                
                <p>We look forward to seeing you at WBS 2026!</p>
                
                <p>Best regards,<br>
                <strong>WBS 2026 Organizing Team</strong><br>
                Worklink Consulting</p>
            </div>
            <div class='footer'>
                <p>&copy; 2026 Worklink Consulting. All rights reserved.</p>
                <p>World Branding and Signage Summit | April 24, 2026 | Nigeria, Africa</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Admin notification email template
 */
function getAdminNotificationTemplate($data) {
    $delegateType = $data['delegateType'] === 'local' ? 'Local Delegate' : 'Foreign Delegate';
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #1a237e; color: white; padding: 20px; }
            .content { background: #f9f9f9; padding: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #f0f0f0; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Registration - WBS 2026</h2>
            </div>
            <div class='content'>
                <p><strong>A new delegate has registered for WBS 2026!</strong></p>
                
                <table>
                    <tr><th>Field</th><th>Information</th></tr>
                    <tr><td>Registration Type</td><td>$delegateType</td></tr>
                    <tr><td>Name</td><td>{$data['firstName']} {$data['lastName']}</td></tr>
                    <tr><td>Email</td><td>{$data['email']}</td></tr>
                    <tr><td>Phone</td><td>{$data['phone']}</td></tr>
                    <tr><td>Country</td><td>{$data['country']}</td></tr>
                    <tr><td>Organization</td><td>{$data['organization']}</td></tr>
                    <tr><td>Position</td><td>{$data['position']}</td></tr>
                    <tr><td>Hotel Preference</td><td>" . (!empty($data['hotelChoice']) ? $data['hotelChoice'] : 'N/A') . "</td></tr>
                    <tr><td>Dietary Requirements</td><td>" . (!empty($data['dietary']) ? $data['dietary'] : 'None') . "</td></tr>
                    <tr><td>Expectations</td><td>" . (!empty($data['expectations']) ? $data['expectations'] : 'Not provided') . "</td></tr>
                    <tr><td>Newsletter</td><td>" . (!empty($data['newsletter']) ? 'Yes' : 'No') . "</td></tr>
                </table>
                
        </div>
    </body>
    </html>
    ";
}

/**
 * Save registration to database
 */
function saveRegistration($data) {
    $db = getDBConnection();
    
    if (!$db) {
        error_log("Failed to get database connection");
        return false;
    }
    
    $stmt = $db->prepare("INSERT INTO registrations (
        first_name, last_name, email, phone, country, 
        organization, position, delegate_type, hotel_choice, 
        dietary, expectations, newsletter
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $db->error);
        $db->close();
        return false;
    }
    
    $stmt->bind_param(
        "sssssssssssi",
        $data['firstName'],
        $data['lastName'],
        $data['email'],
        $data['phone'],
        $data['country'],
        $data['organization'],
        $data['position'],
        $data['delegateType'],
        $data['hotelChoice'],
        $data['dietary'],
        $data['expectations'],
        $data['newsletter']
    );
    
    $success = $stmt->execute();
    $insertId = $success ? $stmt->insert_id : false;
    
    $stmt->close();
    $db->close();
    
    return $insertId;
}
