<?php

use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\InstallationController;

$router->post('test-db', [new DatabaseController(), 'test']);
$router->post('install', [new InstallationController(), 'install']);
