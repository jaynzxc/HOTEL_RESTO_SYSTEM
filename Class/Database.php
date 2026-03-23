<?php

class Database
{
    private $statement;
    private $connection;

    public function __construct()
    {
        /**
         * Use Railway environment variables:
         * - For deployed apps: RAILWAY_PRIVATE_DOMAIN
         * - For local testing: MYSQL_PUBLIC_URL (parse it)
         */

        // Check if public URL is available (local testing)
        $publicUrl = getenv('MYSQL_PUBLIC_URL');

        if ($publicUrl) {
            // Parse the public URL: mysql://user:pass@host:port/dbname
            $parts = parse_url($publicUrl);
            $host = $parts['host'];
            $port = $parts['port'] ?? 3306;
            $user = $parts['user'];
            $password = $parts['pass'];
            $dbname = ltrim($parts['path'], '/');
        } else {
            // Use private Railway variables (inside deployed Railway)
            $host = getenv('RAILWAY_PRIVATE_DOMAIN');
            $port = getenv('MYSQL_PORT') ?: 3306;
            $user = getenv('MYSQLUSER') ?: 'root';
            $password = getenv('MYSQL_ROOT_PASSWORD') ?: '';
            $dbname = getenv('MYSQL_DATABASE') ?: 'railway';
        }

        // PDO connection
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $this->connection = new PDO($dsn, $user, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public function query($sql, $params = [])
    {
        $this->statement = $this->connection->prepare($sql);
        $this->statement->execute($params);
        return $this;
    }

    public function fetchAll()
    {
        return $this->statement->fetchAll();
    }

    public function fetchOne()
    {
        return $this->statement->fetch();
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    public function rowCount()
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