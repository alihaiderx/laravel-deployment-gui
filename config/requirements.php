<?php

return [
    'php' => '8.0',
    'extensions' => [
        ['name' => 'bcmath', 'required' => true],
        ['name' => 'ctype', 'required' => true],
        ['name' => 'fileinfo', 'required' => true],
        ['name' => 'json', 'required' => true],
        ['name' => 'mbstring', 'required' => true],
        ['name' => 'openssl', 'required' => true],
        ['name' => 'pdo', 'required' => true],
        ['name' => 'pdo_mysql', 'required' => true],
        ['name' => 'tokenizer', 'required' => true],
        ['name' => 'xml', 'required' => true],
        ['name' => 'curl', 'required' => false],
        ['name' => 'zip', 'required' => false],
    ],
];
