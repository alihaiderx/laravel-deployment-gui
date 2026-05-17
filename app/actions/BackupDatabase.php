<?php

namespace App\Actions;

use App\Support\Config;

class BackupDatabase
{
    public function backup(string $host, string $name, string $username, string $password): array
    {
        $backupDir = Config::basePath() . DIRECTORY_SEPARATOR . 'backup';

        if (!is_dir($backupDir) && !@mkdir($backupDir, 0755, true)) {
            return ['ok' => false, 'action' => 'backup_db', 'message' => 'Could not create backup directory.'];
        }

        $filename = $backupDir . DIRECTORY_SEPARATOR . $name . '_' . date('Y-m-d_H-i-s') . '.sql';

        $configFile = tempnam(sys_get_temp_dir(), 'mysqldump_');
        file_put_contents($configFile, "[client]\npassword=" . $password . "\n");

        $cmd = sprintf(
            'mysqldump --defaults-extra-file=%s -h %s -u %s %s',
            escapeshellarg($configFile),
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($name)
        );

        exec($cmd, $output, $exitCode);
        @unlink($configFile);

        if ($exitCode !== 0) {
            return ['ok' => false, 'action' => 'backup_db', 'message' => 'Database backup failed. Ensure mysqldump is available in PATH.'];
        }

        file_put_contents($filename, implode("\n", $output));

        return ['ok' => true, 'action' => 'backup_db'];
    }
}
