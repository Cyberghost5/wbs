<?php
/**
 * Email Configuration
 * 
 * Copy this from email_config.example.php and update with your actual credentials
 */

// SMTP Configuration
define('SMTP_HOST', 'mail.wbssummit.com.ng');
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl');
define('SMTP_USERNAME', 'info@wbssummit.com.ng');
define('SMTP_PASSWORD', 'Wbssummit@2025');

// Email Settings
define('FROM_EMAIL', 'info@wbssummit.com.ng');
define('FROM_NAME', 'WBS 2026 Summit');
define('REPLY_TO_EMAIL', 'info@wbssummit.com.ng');
define('ADMIN_EMAIL', 'info@wbssummit.com.ng');

// Application Settings
define('SITE_NAME', 'Global Branding Summit 2026');
define('SITE_URL', 'https://wbssummit.com.ng/');

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