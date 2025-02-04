<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        $config = require __DIR__ . '/drivers.php';
        try {
            if ($config['database']['mysql']['is_default']) {
                if (self::$pdo === null) {
                    self::$pdo = new PDO(
                        "mysql:host=" . ($_ENV['DB_HOST'] ?? 'localhost') .
                            ";dbname=" . ($_ENV['DB_NAME'] ?? 'test') . ";charset=utf8mb4",
                        $_ENV['DB_USER'] ?? 'root',
                        $_ENV['DB_PASS'] ?? '',
                        [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch as associative arrays
                            PDO::ATTR_PERSISTENT         => true,                   // Persistent connection
                        ]
                    );
                }
            } else if ($config['database']['sqlite']['is_default']) {
                if (self::$pdo === null) {
                    self::$pdo = new PDO('sqlite:' . $config['database']['sqlite']['database']);
                }
            } else if ($config['database']['pgsql']['is_default']) {
                if (self::$pdo === null) {
                    self::$pdo = new PDO('pgsql:host=' . $config['database']['pgsql']['host'] . ';dbname=' . $config['database']['pgsql']['database']);
                }
            } else {
                throw new PDOException("Database driver not found");
            }
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
        return self::$pdo;
    }


    public static function disconnect(): void
    {
        self::$pdo = null;
    }
}
