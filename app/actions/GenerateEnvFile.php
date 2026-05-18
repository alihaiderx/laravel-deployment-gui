<?php

namespace App\Actions;

use App\Support\Config;

class GenerateEnvFile
{
    public function generate(string $deployPath, string $url, string $projectName, array $db, string $licenseKey = ''): array
    {
        $exampleFile = Config::basePath()
            . DIRECTORY_SEPARATOR . 'source-code'
            . DIRECTORY_SEPARATOR . 'project'
            . DIRECTORY_SEPARATOR . '.env.example';

        if (!file_exists($exampleFile)) {
            return ['ok' => true, 'action' => 'generate_env'];
        }

        $content = file_get_contents($exampleFile);

        $content = $this->setValue($content, 'APP_NAME', $projectName);
        $content = $this->setValue($content, 'APP_ENV', 'production');
        $content = $this->setValue($content, 'APP_URL', $url);

        if (preg_match('/^APP_SUBFOLDER\s*=/m', $content)) {
            $subfolder = parse_url($url, PHP_URL_PATH) ?: '/';
            $content = $this->setValue($content, 'APP_SUBFOLDER', $subfolder);
        }

        $content = $this->setValue($content, 'DB_HOST', $db['host'] ?? '');
        $content = $this->setValue($content, 'DB_PORT', $db['port'] ?? '3306');
        $content = $this->setValue($content, 'DB_DATABASE', $db['name'] ?? '');
        $content = $this->setValue($content, 'DB_USERNAME', $db['username'] ?? '');
        $content = $this->setValue($content, 'DB_PASSWORD', $db['password'] ?? '');

        if ($licenseKey !== '') {
            $content = $this->setValue($content, 'APP_LC', $licenseKey);
        }

        $appId = Config::get('installation.appId', '');
        if ($appId !== '') {
            $content = $this->setValue($content, 'APP_ID', $appId);
        }

        $appSecret = Config::get('installation.appSecret', '');
        if ($appSecret !== '') {
            $content = $this->setValue($content, 'APP_SECRET', $appSecret);
        }

        file_put_contents($deployPath . DIRECTORY_SEPARATOR . '.env', $content);

        return ['ok' => true, 'action' => 'generate_env'];
    }

    private function setValue(string $content, string $key, string $value): string
    {
        $quoted = preg_match('/\s/', $value) ? '"' . $value . '"' : $value;
        $line = $key . '=' . $quoted;

        if (preg_match('/^' . preg_quote($key, '/') . '\s*=/m', $content)) {
            return preg_replace('/^' . preg_quote($key, '/') . '\s*=.*/m', $line, $content);
        }

        return rtrim($content) . "\n" . $line . "\n";
    }
}
