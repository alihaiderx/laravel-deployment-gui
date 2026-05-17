<?php

namespace App\Actions;

use App\Support\Config;
use PDO;
use PDOException;

class ImportDatabase
{
    public function import(string $host, string $name, string $username, string $password): array
    {
        $sqlFile = Config::basePath() . DIRECTORY_SEPARATOR . 'source-code' . DIRECTORY_SEPARATOR . 'db.sql';

        if (!file_exists($sqlFile)) {
            return ['ok' => false, 'action' => 'import_db', 'message' => 'db.sql not found in source-code/.'];
        }

        $sql = file_get_contents($sqlFile);

        if ($sql === false || trim($sql) === '') {
            return ['ok' => false, 'action' => 'import_db', 'message' => 'db.sql is empty or unreadable.'];
        }

        try {
            $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => true,
            ]);

            $pdo->exec($sql);
        } catch (PDOException $e) {
            return ['ok' => false, 'action' => 'import_db', 'message' => $e->getMessage()];
        }

        return ['ok' => true, 'action' => 'import_db'];
    }
}
