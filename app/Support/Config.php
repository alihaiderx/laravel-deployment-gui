<?php

namespace App\Support;

class Config
{
    private static array $items = [];
    private static string $basePath = '';

    public static function load(string $path): void
    {
        self::$basePath = dirname($path);
        foreach (glob($path . '/*.php') as $file) {
            $key = basename($file, '.php');
            self::$items[$key] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public static function deployBasePath(): string
    {
        return self::get('app.deployToParent', false)
            ? dirname(self::$basePath)
            : self::$basePath;
    }

    public static function deployPath(): string
    {
        $name = self::get('app.name', 'app');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', trim($name)));
        $folder = $slug . '-' . time();

        return self::deployBasePath() . DIRECTORY_SEPARATOR . $folder;
    }
}
