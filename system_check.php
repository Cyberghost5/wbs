<?php
/**
 * System Check - Verify all components are working
 * Access: http://localhost/wbs/system_check.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>System Check - WBS 2026</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #1a237e; margin-bottom: 30px; }
        .check-item { padding: 15px; margin: 10px 0; border-radius: 5px; display: flex; align-items: center; }
        .check-item.pass { background: #e8f5e9; border-left: 5px solid #4caf50; }
        .check-item.fail { background: #ffebee; border-left: 5px solid #f44336; }
        .check-item.warn { background: #fff3e0; border-left: 5px solid #ff9800; }
        .icon { font-size: 24px; margin-right: 15px; }
        .details { font-size: 13px; color: #666; margin-top: 5px; }
        .section { margin: 30px 0; }
        .section h2 { color: #333; font-size: 18px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #1a237e; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 WBS 2026 System Check</h1>

        <div class="section">
            <h2>PHP Environment</h2>
            <?php
            // PHP Version
            $phpVersion = phpversion();
            $phpOk = version_compare($phpVersion, '7.4.0', '>=');
            echo '<div class="check-item ' . ($phpOk ? 'pass' : 'fail') . '">';
            echo '<div class="icon">' . ($phpOk ? '✅' : '❌') . '</div>';
            echo '<div><strong>PHP Version:</strong> ' . $phpVersion;
            echo '<div class="details">Requirement: PHP 7.4 or higher</div></div></div>';

            // Required extensions
            $extensions = ['mysqli', 'mbstring', 'openssl'];
            foreach ($extensions as $ext) {
                $loaded = extension_loaded($ext);
                echo '<div class="check-item ' . ($loaded ? 'pass' : 'fail') . '">';
                echo '<div class="icon">' . ($loaded ? '✅' : '❌') . '</div>';
                echo '<div><strong>Extension ' . $ext . ':</strong> ' . ($loaded ? 'Loaded' : 'Not loaded') . '</div></div>';
            }
            ?>
        </div>

        <div class="section">
            <h2>File Structure</h2>
            <?php
            $files = [
                'vendor/autoload.php' => 'Composer dependencies',
                'config/email_config.php' => 'Email configuration',
                'config/database.php' => 'Database configuration',
                'api/register.php' => 'Registration API',
                'api/contact.php' => 'Contact API',
                'js/script.js' => 'JavaScript file'
            ];

            foreach ($files as $file => $desc) {
                $exists = file_exists(__DIR__ . '/' . $file);
                echo '<div class="check-item ' . ($exists ? 'pass' : 'fail') . '">';
                echo '<div class="icon">' . ($exists ? '✅' : '❌') . '</div>';
                echo '<div><strong>' . $file . '</strong>';
                echo '<div class="details">' . $desc . '</div></div></div>';
            }
            ?>
        </div>

        <div class="section">
            <h2>Email Configuration</h2>
            <?php
            if (file_exists(__DIR__ . '/config/email_config.php')) {
                require_once __DIR__ . '/config/email_config.php';
                
                $emailChecks = [
                    ['SMTP Host', SMTP_HOST, SMTP_HOST !== 'smtp.gmail.com'],
                    ['SMTP Port', SMTP_PORT, true],
                    ['SMTP Username', SMTP_USERNAME, SMTP_USERNAME !== 'your-email@gmail.com'],
                    ['From Email', FROM_EMAIL, FROM_EMAIL !== 'wbs2026@worklinkconsulting.com'],
                ];

                foreach ($emailChecks as list($label, $value, $isConfigured)) {
                    echo '<div class="check-item ' . ($isConfigured ? 'pass' : 'warn') . '">';
                    echo '<div class="icon">' . ($isConfigured ? '✅' : '⚠️') . '</div>';
                    echo '<div><strong>' . $label . ':</strong> ' . htmlspecialchars($value);
                    if (!$isConfigured) {
                        echo '<div class="details">⚠️ Using default value - please update config/email_config.php</div>';
                    }
                    echo '</div></div>';
                }
            }
            ?>
        </div>

        <div class="section">
            <h2>Database Connection</h2>
            <?php
            if (file_exists(__DIR__ . '/config/database.php')) {
                require_once __DIR__ . '/config/database.php';
                $db = @getDBConnection();
                
                if ($db) {
                    echo '<div class="check-item pass">';
                    echo '<div class="icon">✅</div>';
                    echo '<div><strong>Database Connection:</strong> Successfully connected';
                    echo '<div class="details">Database: wbs_summit</div></div></div>';
                    
                    // Check tables
                    $tables = ['registrations', 'contact_messages'];
                    foreach ($tables as $table) {
                        $result = @$db->query("SHOW TABLES LIKE '$table'");
                        $exists = $result && $result->num_rows > 0;
                        echo '<div class="check-item ' . ($exists ? 'pass' : 'warn') . '">';
                        echo '<div class="icon">' . ($exists ? '✅' : '⚠️') . '</div>';
                        echo '<div><strong>Table ' . $table . ':</strong> ' . ($exists ? 'Exists' : 'Not found');
                        if (!$exists) {
                            echo '<div class="details">⚠️ Run setup_database.php to create tables</div>';
                        }
                        echo '</div></div>';
                    }
                    
                    $db->close();
                } else {
                    echo '<div class="check-item warn">';
                    echo '<div class="icon">⚠️</div>';
                    echo '<div><strong>Database Connection:</strong> Cannot connect';
                    echo '<div class="details">⚠️ Database not required for basic functionality. Run setup_database.php to enable data storage.</div></div></div>';
                }
            }
            ?>
        </div>

        <div class="section">
            <h2>PHPMailer</h2>
            <?php
            if (file_exists(__DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
                echo '<div class="check-item pass">';
                echo '<div class="icon">✅</div>';
                echo '<div><strong>PHPMailer:</strong> Installed correctly</div></div>';
            } else {
                echo '<div class="check-item fail">';
                echo '<div class="icon">❌</div>';
                echo '<div><strong>PHPMailer:</strong> Not found';
                echo '<div class="details">❌ Run "composer install" in the wbs directory</div></div></div>';
            }
            ?>
        </div>

        <div class="section">
            <h2>Quick Actions</h2>
            <div style="padding: 15px;">
                <a href="setup_database.php" style="background: #1a237e; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin: 5px;">
                    Setup Database
                </a>
                <a href="test_email.php" style="background: #ff6b35; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin: 5px;">
                    Test Email
                </a>
                <a href="admin.php" style="background: #4caf50; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin: 5px;">
                    Admin Panel
                </a>
                <a href="index.html" style="background: #2196f3; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin: 5px;">
                    View Website
                </a>
            </div>
        </div>
    </div>
</body>
</html>
