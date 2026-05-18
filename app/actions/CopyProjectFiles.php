<?php

namespace App\Actions;

use App\Support\Config;

class CopyProjectFiles
{
    public function copy(string $deployPath): array
    {
        $sourceProject = Config::basePath() . DIRECTORY_SEPARATOR . 'source-code' . DIRECTORY_SEPARATOR . 'project';
        $sourcePublic = $sourceProject . DIRECTORY_SEPARATOR . 'public';
        $publicDest = dirname(Config::basePath());

        if (!is_dir($sourceProject)) {
            return ['ok' => false, 'action' => 'copy_files', 'message' => 'source-code/project directory not found.'];
        }

        try {
            $this->copyDirectory($sourceProject, $deployPath);

            if (is_dir($sourcePublic)) {
                $this->copyDirectory($sourcePublic, $publicDest);
            }
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'action' => 'copy_files', 'message' => $e->getMessage()];
        }

        return ['ok' => true, 'action' => 'copy_files'];
    }

    private function copyDirectory(string $src, string $dest): void
    {
        if (!is_dir($dest) && !mkdir($dest, 0755, true)) {
            throw new \RuntimeException("Could not create directory: {$dest}");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($target) && !mkdir($target, 0755, true)) {
                    throw new \RuntimeException("Could not create directory: {$target}");
                }
            } else {
                if (!copy($item->getPathname(), $target)) {
                    throw new \RuntimeException("Could not copy file: {$item->getPathname()}");
                }
            }
        }
    }
}
