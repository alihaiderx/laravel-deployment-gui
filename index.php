<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\support\Config;

Config::load(__DIR__ . '/config');

require __DIR__ . '/resources/views/index.php';
