<?php

namespace App\Core;

use Dotenv\Dotenv;
use PDO;
use PDOException;

class Database
{
    private PDO $pdo;
    private static ?self $instance = null;

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
        $dotenv->load();

        $this->dbStart();
    }

    private function dbStart(): void
    {
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->pdo = new PDO("mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASS'], $opt);
        } catch (PDOException $e) {
            die("connection failed: " . $e->getMessage());
        }
    }

    private static function getInstance(): self
    {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    public static function getPdo(): PDO
    {
        return self::getInstance()->pdo;
    }
}
