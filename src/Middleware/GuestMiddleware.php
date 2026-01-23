<?php

namespace App\Middleware;

use App\Interfaces\MiddlewareInterface;
use App\Services\AuthService;
use App\View;


class GuestMiddleware implements MiddlewareInterface{

    private AuthService $auth;

    public function __construct(){
        $this->auth = new AuthService();
    }
    public function handle(): void{
        if($this->auth->isLoggedIn()){
            $role = $this->auth->getCurrentRole();
            header("Location: /{$role}/dashboard");
            exit;
        }
    }
}