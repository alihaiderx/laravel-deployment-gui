<?php

namespace App\Actions;

use PDO;
use PDOException;

class TestDatabaseConnection
{
    public function test(string $host, string $name, string $username, string $password): array
    {
        if ($host === '' || $name === '' || $username === '') {
            return ['ok' => false, 'message' => 'Host, database name, and username are required.'];
        }

        try {
            $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $count = (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()"
            )->fetchColumn();

            return ['ok' => true, 'empty' => $count === 0];
        } catch (PDOException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
