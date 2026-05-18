<?php

namespace App\Http\Controllers;

use App\Actions\ValidateLicense;

class LicenseController
{
    public function validate(): void
    {
        header('Content-Type: application/json');
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $key = trim($body['key'] ?? '');

        if ($key === '') {
            echo json_encode(['ok' => false, 'message' => 'License key is required.']);
            return;
        }

        echo json_encode((new ValidateLicense())->validate($key));
    }
}
