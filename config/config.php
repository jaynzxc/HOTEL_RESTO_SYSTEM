<?php
return [
    'database' => [
        // Use Railway environment variables if available, otherwise fallback to local settings
        'host' => getenv('MYSQLHOST') ?: 'localhost',
        'port' => getenv('MYSQLPORT') ?: '3306',
        'dbname' => getenv('MYSQL_DATABASE') ?: 'hotelRestaurant',
        'charset' => 'utf8mb4',
        'username' => getenv('MYSQLUSER') ?: 'root',
        'password' => getenv('MYSQLPASSWORD') ?: ''
    ],
];