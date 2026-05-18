<?php

use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\LicenseController;

$router->post('test-db', [new DatabaseController(), 'test']);
$router->post('validate-license', [new LicenseController(), 'validate']);
$router->post('install', [new InstallationController(), 'install']);
