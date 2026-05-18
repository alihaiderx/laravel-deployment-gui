<?php

return [
    'php' => '8.0',
    'symlinks' => false,
    'extensions' => [
        ['name' => 'ctype', 'required' => true],
        ['name' => 'curl', 'required' => true],
        ['name' => 'dom', 'required' => true],
        ['name' => 'fileinfo', 'required' => true],
        ['name' => 'json', 'required' => true],
        ['name' => 'mbstring', 'required' => true],
        ['name' => 'openssl', 'required' => true],
        ['name' => 'pdo', 'required' => true],
        ['name' => 'pdo_mysql', 'required' => true],
        ['name' => 'tokenizer', 'required' => true],
        ['name' => 'xml', 'required' => true],
        ['name' => 'bcmath', 'required' => false],
        ['name' => 'gd', 'required' => false],
        ['name' => 'exif', 'required' => false],
        ['name' => 'intl', 'required' => false],
        ['name' => 'zip', 'required' => false],
    ],
];
