<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Support\Config;
use App\Support\Router;

Config::load(__DIR__ . '/config');

$router = new Router();

require __DIR__ . '/routes/web.php';
require __DIR__ . '/routes/api.php';

$router->dispatch();
