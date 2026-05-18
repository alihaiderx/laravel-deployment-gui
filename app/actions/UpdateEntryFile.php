<?php

namespace App\Actions;

use App\Support\Config;

class UpdateEntryFile
{
    public function update(string $deployPath): array
    {
        $indexFile = dirname(Config::basePath()) . DIRECTORY_SEPARATOR . 'index.php';

        if (!file_exists($indexFile)) {
            return ['ok' => false, 'action' => 'update_entry_file', 'message' => 'index.php not found at ' . $indexFile];
        }

        $content = file_get_contents($indexFile);
        $fromDir = dirname(Config::basePath());
        $relPath = $this->relativePath($fromDir, $deployPath);

        $updated = preg_replace(
            "/__DIR__\s*\.\s*'\/\.\.\//",
            "__DIR__.'" . '/' . $relPath . "/",
            $content,
            -1,
            $count
        );

        if ($updated === null || $count === 0) {
            return ['ok' => false, 'action' => 'update_entry_file', 'message' => 'Could not locate path references in index.php to update.'];
        }

        file_put_contents($indexFile, $updated);

        return ['ok' => true, 'action' => 'update_entry_file'];
    }

    private function relativePath(string $from, string $to): string
    {
        $from = rtrim(str_replace('\\', '/', $from), '/');
        $to = rtrim(str_replace('\\', '/', $to), '/');

        $fromParts = explode('/', $from);
        $toParts = explode('/', $to);

        $commonLength = 0;
        $max = min(count($fromParts), count($toParts));
        for ($i = 0; $i < $max; $i++) {
            if ($fromParts[$i] === $toParts[$i]) {
                $commonLength++;
            } else {
                break;
            }
        }

        $ups = array_fill(0, count($fromParts) - $commonLength, '..');
        $downs = array_slice($toParts, $commonLength);

        return implode('/', array_merge($ups, $downs));
    }
}
