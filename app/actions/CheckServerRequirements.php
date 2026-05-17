<?php

namespace App\Actions;

use App\Support\Config;

class CheckServerRequirements
{
    public function check(): array
    {
        return array_merge(
            [$this->checkPhpVersion(Config::get('requirements.php', '8.0'))],
            array_map(
                fn(array $ext) => $this->checkExtension($ext['name'], $ext['required']),
                Config::get('requirements.extensions', [])
            )
        );
    }

    private function checkPhpVersion(string $minVersion): array
    {
        return [
            'label' => 'PHP Version',
            'detail' => '>= ' . $minVersion . ' (current: ' . PHP_VERSION . ')',
            'status' => version_compare(PHP_VERSION, $minVersion, '>='),
            'required' => true,
        ];
    }

    private function checkExtension(string $extension, bool $required): array
    {
        return [
            'label' => $extension,
            'detail' => null,
            'status' => extension_loaded($extension),
            'required' => $required,
        ];
    }
}
