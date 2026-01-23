<?php

namespace App\Core;

use App\Services\AuthService;
use App\View;
use MiddlewareInterface;

class Router
{
    private array $routes = [];


    public function addRoute(string $method, string $path, string $controller, string $action, array $middlewares = []): void
    {
        $this->routes[] = [
            "method" => strtoupper($method),
            "path" => $path,
            "controller" => $controller,
            "action" => $action,
            "middlewares" => $middlewares
        ];
    }
    

    public function dispatch(): void
    {
        $method = $_SERVER["REQUEST_METHOD"];
        $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        if ($uri !== "/") $uri = rtrim($uri, "/");

        foreach ($this->routes as $route) {
            if ($route["method"] == $method && $route["path"] == $uri) {
                foreach($route['middlewares'] as $middleware){
                    if($middleware instanceof MiddlewareInterface){
                        $middleware->handle();
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


    private function render404(): void
    {
        http_response_code(404);
        View::render('errors/404.twig');
    }
}
