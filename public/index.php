<?php
// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration
if (file_exists(__DIR__ . '/../config/database.php')) {
    $config = require __DIR__ . '/../config/database.php';
} else {
    die('Database configuration not found. Please copy config/database.example.php to config/database.php');
}

// Include the router
require_once __DIR__ . '/api.php';