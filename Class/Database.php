<?php

class Database
{
    private $statement;
    private $connection;

    public function __construct()
    {
        // Load Railway environment variables
        $host = getenv('RAILWAY_PRIVATE_DOMAIN') ?: 'localhost';
        $port = getenv('MYSQL_PORT') ?: 3306;
        $dbname = getenv('MYSQL_DATABASE') ?: 'railway';
        $user = getenv('MYSQLUSER') ?: 'root';
        $password = getenv('MYSQL_ROOT_PASSWORD') ?: '';

        // Proper PDO DSN for MySQL
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

        $this->connection = new PDO($dsn, $user, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
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
