<?php

declare(strict_types=1);

/**
 * LaunchStack — Database Configuration
 * Singleton PDO connection with connection pooling support
 */

namespace LaunchStack\Config;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct()
    {
        $this->connect();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void
    {
        $host     = $_ENV['DB_HOST']    ?? '127.0.0.1';
        $port     = $_ENV['DB_PORT']    ?? '3306';
        $database = $_ENV['DB_DATABASE'] ?? 'launchstack_db';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $charset  = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
        ];

        try {
            $this->connection = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Log the error, never expose credentials
            error_log('[LaunchStack DB] Connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed. Please check configuration.', 500);
        }
    }

    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Execute a query safely with prepared statements
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Begin a database transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }

    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup(): void
    {
        throw new \RuntimeException('Cannot unserialize singleton.');
    }
}
