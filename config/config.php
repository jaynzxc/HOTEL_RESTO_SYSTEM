<?php
// Auto-detect if running on Railway or locally
$isRailway = getenv('RAILWAY_ENVIRONMENT') !== false || getenv('MYSQLHOST') !== false;

if ($isRailway) {
    // Railway (Cloud) Configuration
    return [
        'database' => [
            'host' => getenv('MYSQLHOST') ?: '127.0.0.1',
            'port' => getenv('MYSQLPORT') ?: '3306',
            'dbname' => getenv('MYSQLDATABASE') ?: 'railway',
            'charset' => 'utf8mb4'
        ],
        'username' => getenv('MYSQLUSER') ?: 'root',
        'password' => getenv('MYSQLPASSWORD') ?: ''
    ];
} else {
    // Local XAMPP Configuration
    return [
        'database' => [
            'host' => '127.0.0.1',  // Use 127.0.0.1 instead of localhost for TCP/IP
            'port' => '3307',       // Your XAMPP MySQL port
            'dbname' => 'hotelrestaurant',
            'charset' => 'utf8mb4'
        ],
        'username' => 'root',
        'password' => ''
    ];
}
?>