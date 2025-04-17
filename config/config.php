<?php
// Application Name
define('APP_NAME', 'NotesApp');

// Environment Detection
$is_production = false;


// Database Configuration
if ($is_production) {
    // InfinityFree MySQL credentials
    define('DB_HOST', ''); // Replace with your actual InfinityFree MySQL hostname
    define('DB_NAME', 'epiz_notesapp'); // Replace with your actual database name
    define('DB_USER', 'epiz_notesapp'); // Replace with your actual database username
    define('DB_PASS', ''); // Replace with your actual database password
} else {
    // Local Development
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'notesapp');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// Application Configuration
define('APP_URL', 'http://localhost/NotesApp');
define('APP_VERSION', '1.0.0');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'notes_session');

// Security Configuration
define('HASH_ALGO', 'sha256');
define('SALT_LENGTH', 32);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// SMTP Configuration for Email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_EMAIL', '');// Replace with your actual SMTP email
define('SMTP_PASSWORD', '');// Replace with your actual SMTP password

// Error Reporting
if ($is_production) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if ($is_production) {
    ini_set('session.cookie_secure', 1);
}

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
if ($is_production) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
?> 