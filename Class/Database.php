<?php

class Database
{
    private $statement;
    private $connection;

    public function __construct($config, $username = null, $password = null)
    {
        // Handle connection parameters with proper fallbacks
        $host = $config['host'] ?? 'localhost';
        $port = isset($config['port']) && !empty($config['port']) ? $config['port'] : '';
        $dbname = $config['dbname'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';
        
        // Use provided credentials or fallback to config values
        $username = $username ?? ($config['username'] ?? 'root');
        $password = $password ?? ($config['password'] ?? '');
        
        // Build DSN - properly handle port
        $dsn = "mysql:host={$host}";
        if (!empty($port)) {
            $dsn .= ";port={$port}";
        }
        $dsn .= ";dbname={$dbname};charset={$charset}";
        
        // PDO options
        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ];
        
        // Get debug mode from global config if available
        $debug = isset($GLOBALS['config']['debug']) ? $GLOBALS['config']['debug'] : false;
        
        try {
            $this->connection = new PDO($dsn, $username, $password, $options);
            
            // Test the connection
            $this->connection->query("SELECT 1");
            
        } catch (PDOException $e) {
            // Log detailed error information
            error_log("Database connection failed: " . $e->getMessage());
            error_log("DSN attempted: " . $dsn);
            error_log("Host: " . $host);
            error_log("Port: " . $port);
            error_log("Database: " . $dbname);
            error_log("Username: " . $username);
            error_log("Error Code: " . $e->getCode());
            
            // In debug mode, show detailed error
            if ($debug) {
                throw new Exception("Database Connection Error:<br>
                                    Message: " . $e->getMessage() . "<br>
                                    DSN: " . htmlspecialchars($dsn) . "<br>
                                    Host: " . htmlspecialchars($host) . "<br>
                                    Port: " . htmlspecialchars($port) . "<br>
                                    Database: " . htmlspecialchars($dbname));
            }
            
            // In production, throw generic error
            throw new PDOException("Unable to connect to the database. Please try again later.", 0, $e);
        }
    }

    // Rest of your methods remain the same...
    public function query($query, $param = [])
    {
        $this->statement = $this->connection->prepare($query);
        $this->statement->execute($param);
        return $this;
    }

    public function find()
    {
        return $this->statement->fetchAll();
    }

    public function fetch_one()
    {
        return $this->statement->fetch();
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    public function count()
    {
        return $this->statement->rowCount();
    }

    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    public function commit()
    {
        return $this->connection->commit();
    }

    public function rollBack()
    {
        return $this->connection->rollBack();
    }

    public function inTransaction()
    {
        return $this->connection->inTransaction();
    }
}