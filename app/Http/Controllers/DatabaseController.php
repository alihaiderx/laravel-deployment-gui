<?php

namespace App\Http\Controllers;

use App\Actions\TestDatabaseConnection;

class DatabaseController
{
    public function test(): void
    {
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        echo json_encode((new TestDatabaseConnection())->test(
            $body['host'] ?? '',
            $body['name'] ?? '',
            $body['username'] ?? '',
            $body['password'] ?? ''
        ));
    }
}
