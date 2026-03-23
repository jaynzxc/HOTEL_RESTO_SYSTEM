<?php
// test_db.php - Place this in your root directory

echo "<h1>Database Connection Test</h1>";

// Check environment variables
echo "<h2>Environment Variables:</h2>";
echo "<pre>";
echo "RAILWAY_ENVIRONMENT: " . (getenv('RAILWAY_ENVIRONMENT') ?: 'NOT SET') . "\n";
echo "MYSQLHOST: " . (getenv('MYSQLHOST') ?: 'NOT SET') . "\n";
echo "MYSQLPORT: " . (getenv('MYSQLPORT') ?: 'NOT SET') . "\n";
echo "MYSQL_DATABASE: " . (getenv('MYSQL_DATABASE') ?: 'NOT SET') . "\n";
echo "MYSQLUSER: " . (getenv('MYSQLUSER') ?: 'NOT SET') . "\n";
echo "MYSQLPASSWORD: " . (getenv('MYSQLPASSWORD') ? 'SET (value hidden)' : 'NOT SET') . "\n";
echo "</pre>";

// Try different connection methods
echo "<h2>Connection Attempts:</h2>";

// Method 1: Using environment variables directly
echo "<h3>Method 1: Using environment variables directly</h3>";
try {
    $host = getenv('MYSQLHOST');
    $port = getenv('MYSQLPORT');
    $dbname = getenv('MYSQL_DATABASE');
    $username = getenv('MYSQLUSER');
    $password = getenv('MYSQLPASSWORD');
    
    echo "Attempting to connect to: mysql:host={$host};port={$port};dbname={$dbname}<br>";
    
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<span style='color:green'>✓ CONNECTION SUCCESSFUL!</span><br>";
    
    // Test query
    $stmt = $pdo->query("SELECT DATABASE() as db, NOW() as time");
    $result = $stmt->fetch();
    echo "Connected to database: " . $result['db'] . "<br>";
    echo "Server time: " . $result['time'] . "<br>";
    
} catch (PDOException $e) {
    echo "<span style='color:red'>✗ CONNECTION FAILED: " . $e->getMessage() . "</span><br>";
}

// Method 2: Using Railway's private domain
echo "<h3>Method 2: Using RAILWAY_PRIVATE_DOMAIN</h3>";
try {
    $host = getenv('RAILWAY_PRIVATE_DOMAIN');
    $port = '3306';
    $dbname = getenv('MYSQL_DATABASE');
    $username = getenv('MYSQLUSER');
    $password = getenv('MYSQLPASSWORD');
    
    if ($host) {
        echo "Attempting to connect to: mysql:host={$host};port={$port};dbname={$dbname}<br>";
        
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<span style='color:green'>✓ CONNECTION SUCCESSFUL!</span><br>";
    } else {
        echo "RAILWAY_PRIVATE_DOMAIN not set<br>";
    }
} catch (PDOException $e) {
    echo "<span style='color:red'>✗ CONNECTION FAILED: " . $e->getMessage() . "</span><br>";
}

// Method 3: Using TCP proxy domain (if available)
echo "<h3>Method 3: Using RAILWAY_TCP_PROXY_DOMAIN</h3>";
try {
    $host = getenv('RAILWAY_TCP_PROXY_DOMAIN');
    $port = getenv('RAILWAY_TCP_PROXY_PORT');
    $dbname = getenv('MYSQL_DATABASE');
    $username = getenv('MYSQLUSER');
    $password = getenv('MYSQLPASSWORD');
    
    if ($host && $port) {
        echo "Attempting to connect to: mysql:host={$host};port={$port};dbname={$dbname}<br>";
        
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<span style='color:green'>✓ CONNECTION SUCCESSFUL!</span><br>";
    } else {
        echo "RAILWAY_TCP_PROXY_DOMAIN or RAILWAY_TCP_PROXY_PORT not set<br>";
    }
} catch (PDOException $e) {
    echo "<span style='color:red'>✗ CONNECTION FAILED: " . $e->getMessage() . "</span><br>";
}

echo "<h2>PHP Information:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO MySQL enabled: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";
echo "MySQLnd enabled: " . (extension_loaded('mysqlnd') ? 'Yes' : 'No') . "<br>";
?>