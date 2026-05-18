<?php

namespace App\Actions;

use PDO;
use PDOException;

class TestDatabaseConnection
{
    private function humanize(string $message, string $host, string $port): string
    {
        if (str_contains($message, '2002')) {
            return "Could not connect to {$host}:{$port}. Check that the host and port are correct and the server is reachable.";
        }
        if (str_contains($message, '1045')) {
            return 'Access denied. Check that the username and password are correct.';
        }
        if (str_contains($message, '1049')) {
            return 'Database not found. Check that the database name is correct.';
        }
        return $message;
    }

    public function test(string $host, string $port, string $name, string $username, string $password): array
    {
        if ($host === '' || $name === '' || $username === '') {
            return ['ok' => false, 'message' => 'Host, database name, and username are required.'];
        }

        $port = $port !== '' ? $port : '3306';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_CONNECT_TIMEOUT => 5,
            ]);

            $count = (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()"
            )->fetchColumn();

            return ['ok' => true, 'empty' => $count === 0];
        } catch (PDOException $e) {
            return ['ok' => false, 'message' => $this->humanize($e->getMessage(), $host, $port)];
        }
    }
}
