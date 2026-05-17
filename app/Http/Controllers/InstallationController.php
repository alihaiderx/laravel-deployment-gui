<?php

namespace App\Http\Controllers;

use App\Actions\BackupDatabase;
use App\Actions\ImportDatabase;
use App\Actions\CopyProjectFiles;
use App\Actions\CreateSymlinks;
use App\Actions\GenerateEnvFile;
use App\Support\Config;

class InstallationController
{
    public function install(): void
    {
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $db = $body['db'] ?? [];
        $dbWasEmpty = $body['dbWasEmpty'] ?? true;
        $url = $body['url'] ?? '';

        $deployPath = Config::deployPath();
        $results = [];

        if (!$dbWasEmpty) {
            $result = (new BackupDatabase())->backup(
                $db['host'] ?? '',
                $db['name'] ?? '',
                $db['username'] ?? '',
                $db['password'] ?? ''
            );
            $results[] = $result;
            if (!$result['ok']) {
                echo json_encode(['ok' => false, 'message' => $result['message'], 'results' => $results]);
                return;
            }
        }

        $steps = [
            fn() => (new ImportDatabase())->import($db['host'] ?? '', $db['name'] ?? '', $db['username'] ?? '', $db['password'] ?? ''),
            fn() => (new CopyProjectFiles())->copy($deployPath),
            fn() => (new CreateSymlinks())->create($deployPath),
            fn() => (new GenerateEnvFile())->generate($deployPath, $url),
        ];

        foreach ($steps as $step) {
            $result = $step();
            $results[] = $result;
            if (!$result['ok']) {
                echo json_encode(['ok' => false, 'message' => $result['message'], 'results' => $results]);
                return;
            }
        }

        echo json_encode(['ok' => true, 'deployPath' => $deployPath, 'results' => $results]);
    }
}
