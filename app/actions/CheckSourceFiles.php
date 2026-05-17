<?php

namespace App\Actions;

use App\Support\Config;

class CheckSourceFiles
{
    public function check(): array
    {
        $sourceDir = Config::basePath() . DIRECTORY_SEPARATOR . 'source-code';
        $requireDb = Config::get('installation.requireDbFile', true);

        return [
            $this->checkProjectDir($sourceDir),
            $this->checkDbFile($sourceDir, $requireDb),
        ];
    }

    private function checkProjectDir(string $sourceDir): array
    {
        return [
            'label' => 'Project source directory',
            'detail' => 'source-code/project',
            'status' => is_dir($sourceDir . DIRECTORY_SEPARATOR . 'project'),
            'required' => true,
        ];
    }

    private function checkDbFile(string $sourceDir, bool $required): array
    {
        return [
            'label' => 'Database SQL file',
            'detail' => 'source-code/db.sql',
            'status' => file_exists($sourceDir . DIRECTORY_SEPARATOR . 'db.sql'),
            'required' => $required,
        ];
    }
}
