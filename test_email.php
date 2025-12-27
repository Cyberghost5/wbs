<?php
/**
 * Test Email Configuration
 * Run this to test if your SMTP settings are working
 * Access: http://localhost/wbs/test_email.php
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Configuration Test - WBS 2026</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #1a237e; }
        .success { background: #4caf50; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .error { background: #f44336; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info { background: #2196f3; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .config { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; font-family: monospace; }
        .btn { background: #1a237e; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #0d1642; }
        form { margin: 20px 0; }
        input[type="email"] { width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Configuration Test</h1>
        
        <div class="config">
            <strong>Current Configuration:</strong><br>
            SMTP Host: <?php echo SMTP_HOST; ?><br>
            SMTP Port: <?php echo SMTP_PORT; ?><br>
            SMTP Secure: <?php echo SMTP_SECURE; ?><br>
            Username: <?php echo SMTP_USERNAME; ?><br>
            From Email: <?php echo FROM_EMAIL; ?><br>
            From Name: <?php echo FROM_NAME; ?>
        </div>

        <?php
        if (isset($_POST['test_email'])) {
            $testEmail = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            
            if (!$testEmail) {
                echo '<div class="error">❌ Invalid email address!</div>';
            } else {
                $mail = new PHPMailer(true);
                
                try {
                    // Enable verbose debug output
                    $mail->SMTPDebug = 0; // Set to 2 for detailed debug
                    
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
                    $mail->addAddress($testEmail);
                    $mail->addReplyTo(REPLY_TO_EMAIL, FROM_NAME);
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'WBS 2026 - Email Configuration Test';
                    $mail->Body = "
                        <h2>Email Test Successful! ✅</h2>
                        <p>Your WBS 2026 email configuration is working correctly.</p>
                        <p><strong>SMTP Details:</strong></p>
                        <ul>
                            <li>Host: " . SMTP_HOST . "</li>
                            <li>Port: " . SMTP_PORT . "</li>
                            <li>Secure: " . SMTP_SECURE . "</li>
                        </ul>
                        <p>You can now use the registration and contact forms!</p>
                    ";
                    $mail->AltBody = 'WBS 2026 Email Configuration Test - Success!';
                    
                    $mail->send();
                    echo '<div class="success">✅ Test email sent successfully to ' . htmlspecialchars($testEmail) . '! Check your inbox.</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="error">❌ Email sending failed!<br><br>';
                    echo '<strong>Error:</strong> ' . htmlspecialchars($mail->ErrorInfo) . '<br><br>';
                    echo '<strong>Common Solutions:</strong><br>';
                    echo '• Check SMTP credentials are correct<br>';
                    echo '• For Gmail: Use App Password (not regular password)<br>';
                    echo '• Verify firewall allows outbound connection on port ' . SMTP_PORT . '<br>';
                    echo '• Try changing port (587 for TLS or 465 for SSL)<br>';
                    echo '</div>';
                }
            }
        }
        ?>

        <form method="POST">
            <h3>Send Test Email</h3>
            <input type="email" name="email" placeholder="Enter your email address" required>
            <button type="submit" name="test_email" class="btn">Send Test Email</button>
        </form>

        <div class="info">
            <strong>💡 Troubleshooting Tips:</strong><br>
            • Make sure XAMPP Apache is running<br>
            • Check email credentials in config/email_config.php<br>
            • For Gmail: Enable 2FA and create an App Password<br>
            • Check spam folder for test emails<br>
            • Try running setup_database.php first if you haven't
        </div>
    </div>
</body>
</html>
