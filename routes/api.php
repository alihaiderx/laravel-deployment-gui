<?php

use App\Http\Controllers\DatabaseController;

$router->post('test-db', [new DatabaseController(), 'test']);
