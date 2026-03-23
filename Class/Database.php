<?php
/**
 * Single-file Database connection for Railway MySQL
 * Author: Your Name
 * Works with PDO
 */

// Configuration - replace these with your Railway credentials
$DB_HOST = getenv('MYSQL_HOST') ?: 'centerbeam.proxy.rlwy.net';
$DB_PORT = getenv('MYSQL_PORT') ?: 48627;
$DB_NAME = getenv('MYSQL_DATABASE') ?: 'railway';
$DB_USER = getenv('MYSQL_USER') ?: 'root';
$DB_PASS = getenv('MYSQL_PASSWORD') ?: 'mRUryfEXvYVrnMqNjEOegrBaaZxQTaxj';

class Database
{
    private $connection;
    private $statement;

    public function __construct($host, $port, $dbname, $username, $password)
    {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $this->connection = new PDO($dsn, $username, $password, [
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

// Instantiate the Database
$db = new Database($DB_HOST, $DB_PORT, $DB_NAME, $DB_USER, $DB_PASS);

// Example usage:

// 1️⃣ Test connection
$time = $db->query("SELECT NOW() AS server_time")->fetchOne();
echo "Database Server Time: " . $time['server_time'] . PHP_EOL;

// 2️⃣ Insert example
$db->query("INSERT INTO users (name, email) VALUES (?, ?)", ['Jane Doe', 'jane@example.com']);
echo "Inserted ID: " . $db->lastInsertId() . PHP_EOL;

// 3️⃣ Fetch all users
$users = $db->query("SELECT * FROM users")->fetchAll();
print_r($users);