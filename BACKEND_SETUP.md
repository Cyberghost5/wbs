# WBS 2026 - Backend Setup Guide

This guide will help you configure the PHP backend with PHPMailer for handling registration and contact forms.

## 📋 Prerequisites

- XAMPP (or any PHP 7.4+ environment)
- Composer (PHP package manager)
- An email account with SMTP access (Gmail, Office365, etc.)

## 🚀 Installation Steps

### 1. Install Composer Dependencies

The PHPMailer library has already been installed. If you need to reinstall:

```bash
cd c:\xampp\htdocs\wbs
composer install
```

### 2. Configure Email Settings

Open `config/email_config.php` and update the following settings:

#### For Gmail:

1. **Enable 2-Factor Authentication** in your Google Account
2. **Generate an App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Other (Custom name)"
   - Copy the 16-character password

3. **Update the config file**:
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_SECURE', 'tls');
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-app-password');  // 16-char app password
   ```

#### For Office365/Outlook:

```php
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'your-email@outlook.com');
define('SMTP_PASSWORD', 'your-password');
```

#### For Other SMTP Providers:

Contact your email provider for SMTP settings.

### 3. Update Application Settings

In `config/email_config.php`, update these settings:

```php
define('FROM_EMAIL', 'info@wbssummit.com.ng');
define('FROM_NAME', 'WBS 2026 Summit');
define('REPLY_TO_EMAIL', 'info@wbssummit.com.ng');
define('ADMIN_EMAIL', 'info@wbssummit.com.ng');  // Where form submissions are sent
define('SITE_URL', 'http://localhost/wbs');  // Update for production
```

## 📁 Project Structure

```
wbs/
├── api/
│   ├── register.php       # Registration form handler
│   └── contact.php        # Contact form handler
├── config/
│   ├── email_config.php   # Email configuration (update this!)
│   └── email_config.example.php  # Template for reference
├── vendor/                # Composer dependencies (PHPMailer)
├── js/
│   └── script.js         # Frontend form handling
├── composer.json
└── .gitignore
```

## 🧪 Testing

### Test Registration Form:

1. Start XAMPP (Apache)
2. Open: `http://localhost/wbs/index.html`
3. Click "Register Now"
4. Fill out the form and submit
5. Check:
   - User receives confirmation email
   - Admin receives notification email
   - Console for any errors

### Test Contact Form:

1. Scroll to "Get In Touch" section
2. Fill out the contact form
3. Submit and verify emails are sent

## 🔧 Troubleshooting

### Issue: "SMTP Error: Could not authenticate"

**Solution:**
- Gmail: Ensure you're using an App Password, not your regular password
- Check username and password are correct
- Verify 2FA is enabled for Gmail

### Issue: "Connection refused" or "Could not connect to SMTP host"

**Solution:**
- Check firewall settings
- Verify SMTP host and port are correct
- Try port 465 with SSL instead of 587 with TLS

### Issue: Emails going to spam

**Solution:**
- Use a verified domain email for FROM_EMAIL
- Add SPF and DKIM records to your domain
- Ask recipients to whitelist your email

### Issue: "Failed to send emails"

**Solution:**
- Check PHP error logs: `xampp/apache/logs/error.log`
- Enable error reporting in PHP files (for debugging only):
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```

## 📧 Email Templates

### Registration Confirmation Email

Users receive:
- Welcome message
- Registration details
- Payment instructions (based on delegate type)
- Event information
- Next steps

### Contact Form Confirmation

Users receive:
- Acknowledgment of their message
- Expected response time
- Contact information

### Admin Notifications

Admins receive:
- All form submission details
- Timestamp
- User contact information for follow-up

## 🔒 Security Notes

1. **Never commit `config/email_config.php`** to version control (already in .gitignore)
2. **Use App Passwords** for Gmail instead of your main password
3. **Validate and sanitize** all inputs (already implemented)
4. **Use HTTPS** in production
5. **Rate limiting**: Consider adding to prevent spam

## 🌐 Production Deployment

Before deploying to production:

1. Update `SITE_URL` in email_config.php
2. Change `FROM_EMAIL` to your actual domain email
3. Set up proper error logging
4. Consider adding database storage for registrations
5. Implement rate limiting for forms
6. Add CAPTCHA for spam prevention
7. Use environment variables for sensitive data

## 📝 Adding Database Storage (Optional)

To store registrations in a database:

1. Create database table:
   ```sql
   CREATE TABLE registrations (
       id INT AUTO_INCREMENT PRIMARY KEY,
       first_name VARCHAR(100),
       last_name VARCHAR(100),
       email VARCHAR(255),
       phone VARCHAR(50),
       country VARCHAR(100),
       organization VARCHAR(255),
       position VARCHAR(255),
       delegate_type VARCHAR(20),
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

2. Add database connection in `api/register.php`:
   ```php
   $mysqli = new mysqli('localhost', 'username', 'password', 'wbs_db');
   
   $stmt = $mysqli->prepare("INSERT INTO registrations (first_name, last_name, email, ...) VALUES (?, ?, ?, ...)");
   $stmt->bind_param("sss...", $firstName, $lastName, $email, ...);
   $stmt->execute();
   ```

## 📞 Support

For issues or questions:
- Email: info@wbssummit.com.ng
- Check PHP error logs for detailed error messages

## ✅ Checklist

- [ ] Composer dependencies installed
- [ ] Email config updated with valid SMTP credentials
- [ ] Tested registration form submission
- [ ] Tested contact form submission
- [ ] Verified emails are received by users
- [ ] Verified admin notifications are received
- [ ] Checked spam folders
- [ ] Reviewed error logs

---

**Note:** Make sure XAMPP Apache is running before testing!
