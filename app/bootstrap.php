<?php

// Require Composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables using Dotenv
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(dirname(__DIR__)); // Ensure it loads from the root directory
$dotenv->load();

// Initialize Database Connection (Optional)
require_once __DIR__ . '/config/database.php';

// Start Sessions (If needed)
session_start();

// Load Routes
require_once __DIR__ . '/routes/web.php';
require_once __DIR__ . '/routes/api.php';

// Dispatch Routes
App\Assembly\Core\Route::dispatch();  
