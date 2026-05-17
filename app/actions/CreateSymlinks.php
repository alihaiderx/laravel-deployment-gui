<?php

namespace App\Actions;

use App\Support\Config;

class CreateSymlinks
{
    public function create(string $deployPath): array
    {
        $symlinks = Config::get('installation.symlinks', []);
        $errors = [];

        foreach ($symlinks as $entry) {
            $target = $deployPath . DIRECTORY_SEPARATOR . $entry['target'];
            $link = $deployPath . DIRECTORY_SEPARATOR . $entry['link'];

            $linkDir = dirname($link);
            if (!is_dir($linkDir)) {
                @mkdir($linkDir, 0755, true);
            }

            if (is_link($link) || file_exists($link)) {
                @unlink($link);
            }

            if (!@symlink($target, $link)) {
                $errors[] = "Could not create symlink: {$entry['link']} → {$entry['target']}";
            }
        }

        if (!empty($errors)) {
            return ['ok' => false, 'action' => 'create_symlinks', 'message' => implode('; ', $errors)];
        }

        return ['ok' => true, 'action' => 'create_symlinks'];
    }
}
