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
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load config
require_once dirname(__DIR__) . '/src/config/config.php';
