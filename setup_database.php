<?php
/**
 * Database Setup Script
 * Run this once to create the database and tables
 * Access: http://localhost/wbs/setup_database.php
 */

$mysqli = new mysqli('localhost', 'yexnecom_wbssummit', 'yexnecom_wbssummit');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS yexnecom_wbssummit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($mysqli->query($sql)) {
    echo "✓ Database 'yexnecom_wbssummit' created successfully<br>";
} else {
    echo "✗ Error creating database: " . $mysqli->error . "<br>";
}

// Select database
$mysqli->select_db('yexnecom_wbssummit');

// Create registrations table
$sql = "CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    country VARCHAR(100) NOT NULL,
    organization VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    delegate_type ENUM('local', 'foreign') NOT NULL,
    hotel_choice VARCHAR(50),
    dietary VARCHAR(500),
    expectations TEXT,
    newsletter TINYINT(1) DEFAULT 0,
    terms_accepted TINYINT(1) DEFAULT 1,
    payment_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_delegate_type (delegate_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    echo "✓ Table 'registrations' created successfully<br>";
} else {
    echo "✗ Error creating registrations table: " . $mysqli->error . "<br>";
}

// Create contact_messages table
$sql = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($mysqli->query($sql)) {
    echo "✓ Table 'contact_messages' created successfully<br>";
} else {
    echo "✗ Error creating contact_messages table: " . $mysqli->error . "<br>";
}

$mysqli->close();

echo "<br><strong style='color: green;'>Database setup completed!</strong><br>";
echo "<br>You can now delete this file for security.";
