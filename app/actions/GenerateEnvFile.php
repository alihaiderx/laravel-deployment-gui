<?php

namespace App\Actions;

class GenerateEnvFile
{
    public function generate(string $deployPath, string $url): array
    {
        return ['ok' => true, 'action' => 'generate_env'];
    }
}
