<?php
/**
 * Email Configuration
 * 
 * Copy this from email_config.example.php and update with your actual credentials
 */

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');

// Email Settings
define('FROM_EMAIL', 'info@wbssummit.com.ng');
define('FROM_NAME', 'WBS 2026 Summit');
define('REPLY_TO_EMAIL', 'info@wbssummit.com.ng');
define('ADMIN_EMAIL', 'info@wbssummit.com.ng');

// Application Settings
define('SITE_NAME', 'World Branding and Signage Summit 2026');
define('SITE_URL', 'http://localhost/wbs');

return [
    'smtp' => [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'secure' => SMTP_SECURE,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
    ],
    'from' => [
        'email' => FROM_EMAIL,
        'name' => FROM_NAME,
    ],
    'reply_to' => REPLY_TO_EMAIL,
    'admin_email' => ADMIN_EMAIL,
    'site' => [
        'name' => SITE_NAME,
        'url' => SITE_URL,
    ],
];