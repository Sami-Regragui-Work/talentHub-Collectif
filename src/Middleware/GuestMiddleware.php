<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\View;
use MiddlewareInterface;

class GuestMiddleware implements MiddlewareInterface{

    private AuthService $auth;

    public function __construct(){
        $this->auth = new AuthService();
    }
    public function handle(): void{
        if($this->auth->isLoggedIn()){
            $role = $this->auth->getCurrentRole();
            View::render("/{$role}/dashboard.twig");
            exit;
        }
    }
}