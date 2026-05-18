<?php

namespace App\Actions;

use App\Support\Config;

class CheckPermissions
{
    public function check(): array
    {
        return [
            $this->checkDeployDirectory(),
            $this->checkSymlinkSupport(),
        ];
    }

    private function checkDeployDirectory(): array
    {
        $path = Config::deployBasePath();
        $label = Config::get('app.deployToParent', false)
            ? 'Deploy base directory is writable'
            : 'Parent directory is writable';

        return [
            'label' => $label,
            'detail' => $path,
            'status' => is_writable($path),
            'required' => true,
        ];
    }

    private function checkSymlinkSupport(): array
    {
        $base = Config::deployBasePath();
        $target = $base . DIRECTORY_SEPARATOR . '.symtest_target_' . uniqid();
        $link = $base . DIRECTORY_SEPARATOR . '.symtest_link_' . uniqid();

        if (!@mkdir($target)) {
            return [
                'label' => 'Symlinks supported',
                'detail' => 'Could not create test directory in deploy path',
                'status' => false,
                'required' => false,
            ];
        }

        $success = @symlink($target, $link);

        if ($success) {
            PHP_OS_FAMILY === 'Windows' ? @rmdir($link) : @unlink($link);
        }
        @rmdir($target);

        $detail = (!$success && PHP_OS_FAMILY === 'Windows')
            ? 'On Windows, symlinks require Developer Mode or administrator privileges'
            : null;

        return [
            'label' => 'Symlinks supported',
            'detail' => $detail,
            'status' => $success,
            'required' => Config::get('requirements.symlinks', false),
        ];
    }
}
