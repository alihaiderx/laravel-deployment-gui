<?php

namespace App\Actions;

use App\Support\Config;
use PDO;
use PDOException;

class BackupDatabase
{
    public function backup(string $host, string $port, string $name, string $username, string $password): array
    {
        $backupDir = Config::basePath() . DIRECTORY_SEPARATOR . 'backup';

        if (!is_dir($backupDir) && !@mkdir($backupDir, 0755, true)) {
            return ['ok' => false, 'action' => 'backup_db', 'message' => 'Could not create backup directory.'];
        }

        $port = $port !== '' ? $port : '3306';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $sql = "-- Backup of `{$name}` generated " . date('Y-m-d H:i:s') . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $sql .= $this->dumpTable($pdo, $table);
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $filename = $backupDir . DIRECTORY_SEPARATOR . $name . '_' . date('Y-m-d_H-i-s') . '.sql';
            file_put_contents($filename, $sql);
        } catch (PDOException $e) {
            return ['ok' => false, 'action' => 'backup_db', 'message' => 'Backup failed: ' . $e->getMessage()];
        }

        return ['ok' => true, 'action' => 'backup_db'];
    }

    private function dumpTable(PDO $pdo, string $table): string
    {
        $quoted = "`{$table}`";
        $sql = "DROP TABLE IF EXISTS {$quoted};\n";

        $create = $pdo->query("SHOW CREATE TABLE {$quoted}")->fetch(PDO::FETCH_ASSOC);
        $sql .= $create['Create Table'] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM {$quoted}")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return $sql . "\n";
        }

        $columns = '`' . implode('`, `', array_keys($rows[0])) . '`';
        $sql .= "INSERT INTO {$quoted} ({$columns}) VALUES\n";

        $valueLines = [];
        foreach ($rows as $row) {
            $values = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $row);
            $valueLines[] = '(' . implode(', ', $values) . ')';
        }

        $sql .= implode(",\n", $valueLines) . ";\n\n";

        return $sql;
    }
}
