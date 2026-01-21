<?php

namespace App\Core;

use App\Services\AuthService;
use App\View;

class Router
{
    private array $routes = [];
    private AuthService $auth_service;

    public function __construct()
    {
        $this->auth_service = new AuthService();
    }

    public function addRoute(string $method, string $path, string $controller, string $action, string $user_role = ""): void
    {
        $this->routes[] = [
            "method" => strtoupper($method),
            "path" => $path,
            "controller" => $controller,
            "action" => $action,
            "user_role" => $user_role,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER["REQUEST_METHOD"];
        $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        if ($uri !== "/") $uri = rtrim($uri, "/");

        foreach ($this->routes as $route) {
            if ($route["method"] == $method && $route["path"] == $uri) {
                if (!empty($route["user_role"])) {
                    if (!$this->auth_service->isLoggedIn()) {
                        header("Location: /login");
                        exit;
                    }

                    if ($this->auth_service->getCurrentRole() !== $route["user_role"]) {
                        $this->render403();
                        exit;
                    }
                }
                $controller = new $route["controller"]();
                $action = $route["action"];
                $controller->$action();
                return;
            }
        }
        $this->render404();
    }

    private function render403(): void
    {
        http_response_code(403);
        View::render('errors/403.twig');
    }

    private function render404(): void
    {
        http_response_code(404);
        View::render('errors/404.twig');
    }
}
