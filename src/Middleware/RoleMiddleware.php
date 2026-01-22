<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\View;
use MiddlewareInterface;
class RoleMiddleware implements MiddlewareInterface{
    private AuthService $auth;
    private string $role;

    public function __construct(string $role){
        $this->auth = new AuthService();
        $this->role = $role;
    }

    public function handle():void{
        if($this->auth->getCurrentRole() !== $this->role){
            http_response_code(403);
            View::render('errors/403.twig');
            exit;
        }
    }

}