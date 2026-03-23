<?php

class Database
{
    private $statement;
    private $connection;

    public function __construct($config, $username = 'root', $password = '')
    {
        // Handle the connection parameters properly
        $host = $config['host'] ?? 'localhost';
        $port = isset($config['port']) ? ';port=' . $config['port'] : '';
        $dbname = $config['dbname'] ?? '';
        
        // Build DSN - use host and port, avoid socket issues
        $dsn = "mysql:host={$host}{$port};dbname={$dbname}";
        
        // Add charset for better compatibility
        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Add this for better error reporting
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        try {
            $this->connection = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Log the error properly
            error_log("Database connection failed: " . $e->getMessage());
            throw $e;
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