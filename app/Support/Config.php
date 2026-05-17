<?php

namespace App\Support;

class Config
{
    private static array $items = [];

    public static function load(string $path): void
    {
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
}
