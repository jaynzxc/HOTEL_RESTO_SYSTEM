<?php
/**
 * Configuration File - Robust version with better error handling
 */

// Detect if we're running on Railway
$isRailway = !empty(getenv('RAILWAY_ENVIRONMENT')) || !empty(getenv('MYSQLHOST'));

if ($isRailway) {
    // Try multiple possible host sources for Railway
    $host = getenv('MYSQLHOST');
    $port = getenv('MYSQLPORT');
    
    // If MYSQLHOST is not set, try RAILWAY_PRIVATE_DOMAIN
    if (empty($host)) {
        $host = getenv('RAILWAY_PRIVATE_DOMAIN');
        $port = '3306';
    }
    
    // If still empty, try TCP proxy
    if (empty($host)) {
        $host = getenv('RAILWAY_TCP_PROXY_DOMAIN');
        $port = getenv('RAILWAY_TCP_PROXY_PORT');
    }
    
    // Final fallback
    if (empty($host)) {
        $host = 'mysql';
        $port = '3306';
    }
    
    $config = [
        'database' => [
            'host' => $host,
            'port' => $port,
            'dbname' => getenv('MYSQL_DATABASE') ?: 'railway',
            'charset' => 'utf8mb4',
            'username' => getenv('MYSQLUSER') ?: 'root',
            'password' => getenv('MYSQLPASSWORD') ?: ''
        ],
        'environment' => 'production',
        'debug' => true // Set to true temporarily for debugging
    ];
    
    // Log the connection attempt
    error_log("Railway Database Config - Host: {$host}, Port: {$port}, DB: " . $config['database']['dbname']);
    
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