<?php

class Database
{
    private $statement;
    private $connection;

    public function __construct($config, $username = null, $password = null)
    {
        // Handle connection parameters with proper fallbacks
        $host = $config['host'] ?? 'localhost';
        $port = isset($config['port']) && !empty($config['port']) ? ';port=' . $config['port'] : '';
        $dbname = $config['dbname'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';
        
        // Use provided credentials or fallback to config values
        $username = $username ?? ($config['username'] ?? 'root');
        $password = $password ?? ($config['password'] ?? '');
        
        // Build DSN - use host and port, avoid socket issues
        $dsn = "mysql:host={$host}{$port};dbname={$dbname};charset={$charset}";
        
        // PDO options
        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false
        ];
        
        try {
            $this->connection = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Log the error
            error_log("Database connection failed: " . $e->getMessage());
            error_log("DSN attempted: " . $dsn);
            error_log("Host: " . $host);
            error_log("Port: " . $port);
            
            // Re-throw the exception
            throw $e;
        }
    }

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