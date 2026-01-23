<?php



namespace App\Middleware;

use App\Services\AuthService;
use App\View;
use MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface{

    private AuthService $auth;

    public function __construct(){
        $this->auth = new AuthService();
    }
    public function handle():void{
        if(!$this->auth->isLoggedIn()){
            header('Location: /login');
            exit;
        }
    }

}




