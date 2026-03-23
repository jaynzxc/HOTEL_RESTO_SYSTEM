<?php
/**
 * Configuration File
 * Works both locally and on Railway
 */

// Detect if we're running on Railway
$isRailway = !empty(getenv('RAILWAY_ENVIRONMENT')) || !empty(getenv('MYSQLHOST'));

if ($isRailway) {
    // Railway production configuration
    $config = [
        'database' => [
            'host' => getenv('MYSQLHOST'),
            'port' => getenv('MYSQLPORT'),
            'dbname' => getenv('MYSQL_DATABASE'),
            'charset' => 'utf8mb4',
            'username' => getenv('MYSQLUSER'),
            'password' => getenv('MYSQLPASSWORD')
        ],
        'environment' => 'production',
        'debug' => false
    ];
    
    // Validate Railway database configuration
    if (empty($config['database']['host']) || empty($config['database']['dbname'])) {
        error_log("Railway: Missing database configuration. Check environment variables.");
        $config['database']['host'] = 'mysql';
        $config['database']['port'] = '3306';
        $config['database']['dbname'] = 'railway';
        $config['database']['username'] = 'root';
        $config['database']['password'] = '';
    }
} else {
    // Local development configuration
    $config = [
        'database' => [
            'host' => 'localhost',
            'port' => '3306',
            'dbname' => 'hotelRestaurant',
            'charset' => 'utf8mb4',
            'username' => 'root',
            'password' => ''
        ],
        'environment' => 'development',
        'debug' => true
    ];
}

// Add any additional configuration settings
$config['app'] = [
    'name' => 'Hotel & Restaurant Management System',
    'version' => '1.0.0',
    'timezone' => 'Asia/Manila'
];

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Enable error reporting for development
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

return $config;
?>