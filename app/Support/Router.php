<?php

namespace App\Support;

class Router
{
    private array $routes = [];

    public function get(string $action, callable $handler): void
    {
        $this->routes['GET'][$action] = $handler;
    }

    public function post(string $action, callable $handler): void
    {
        $this->routes['POST'][$action] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        if ($action === '') {
            ($this->routes['GET'][''] ?? fn() => null)();
            return;
        }

        $handler = $this->routes[$method][$action] ?? null;

        if ($handler === null) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not found']);
            return;
        }

        $handler();
    }
}
