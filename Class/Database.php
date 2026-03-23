<?php

class Database
{
    private $statement;
    private $connection;

    public function __construct($config, $username = null, $password = null)
    {
        // If username/password not provided, try to get from config
        if ($username === null && isset($config['username'])) {
            $username = $config['username'];
        } elseif ($username === null) {
            $username = 'root';
        }
        
        if ($password === null && isset($config['password'])) {
            $password = $config['password'];
        } elseif ($password === null) {
            $password = '';
        }
        
        // Extract database connection details
        $host = $config['database']['host'] ?? '127.0.0.1';
        $port = $config['database']['port'] ?? '3306';
        $dbname = $config['database']['dbname'] ?? 'hotelrestaurant';
        $charset = $config['database']['charset'] ?? 'utf8mb4';
        
        // Build DSN
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        
        try {
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            // For debugging - remove in production
            die("Connection failed: " . $e->getMessage() . 
                "<br>DSN: " . $dsn . 
                "<br>Username: " . $username);
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