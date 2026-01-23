<?php
/**
 * Bootstrap file for PHPUnit tests
 */

// Start session for tests that require it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mock session data for tests
$_SESSION['user_id'] = 1;
$_SESSION['type'] = 'super';

// Load composer autoloader
require_once '/var/www/html/vendor/autoload.php';

// Load config (in container, files are in /var/www/html/ not /var/www/html/src/)
require_once '/var/www/html/config/config.php';
