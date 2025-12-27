<?php
/**
 * Email Configuration
 * 
 * IMPORTANT: Update these settings with your actual SMTP credentials
 * For Gmail: You need to enable "App Passwords" in your Google Account
 * For other providers: Use their SMTP settings
 */

// SMTP Configuration
define('SMTP_HOST', 'mail.wbssummit.com.ng');  // Change to your SMTP host (e.g., smtp.gmail.com, smtp.office365.com)
define('SMTP_PORT', 465);                // 587 for TLS, 465 for SSL
define('SMTP_SECURE', 'ssl');            // 'tls' or 'ssl'
define('SMTP_USERNAME', 'info@wbssummit.com.ng');  // Your email address
define('SMTP_PASSWORD', 'Wbssummit@2025');     // Your email password or app-specific password

// Email Settings
define('FROM_EMAIL', 'info@wbssummit.com.ng');  // Sender email
define('FROM_NAME', 'WBS 2026 Summit');                   // Sender name
define('REPLY_TO_EMAIL', 'info@wbssummit.com.ng'); // Reply-to email
define('ADMIN_EMAIL', 'info@wbssummit.com.ng');    // Admin notification email

// Application Settings
define('SITE_NAME', 'Global Branding and Signage Summit 2026');
define('SITE_URL', 'https://wbssummit.com.ng');

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
