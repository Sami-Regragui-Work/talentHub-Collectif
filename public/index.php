<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\CandidateController;
use App\Controllers\RecruiterController;
use App\Controllers\AdminController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RoleMiddleware;

session_start();

$router = new Router();

$router->addRoute('GET', '/', AuthController::class, 'showLogin',[new GuestMiddleware()]);
$router->addRoute('GET', '/register', AuthController::class, 'showRegister',[new GuestMiddleware()]);
$router->addRoute('POST', '/register', AuthController::class, 'register',[new GuestMiddleware()]);
$router->addRoute('GET', '/login', AuthController::class, 'showLogin',[new GuestMiddleware()]);
$router->addRoute('POST', '/login', AuthController::class, 'login',[new GuestMiddleware()]);
$router->addRoute('GET', '/logout', AuthController::class, 'logout',[new AuthMiddleware()]);

$router->addRoute('GET', '/candidate/dashboard', CandidateController::class, 'dashboard',[new AuthMiddleware() , new RoleMiddleware('candidate')] );

$router->addRoute('GET', '/recruiter/dashboard', RecruiterController::class, 'dashboard',[new AuthMiddleware() , new RoleMiddleware('recruiter')] );

$router->addRoute('GET', '/admin/dashboard', AdminController::class, 'dashboard',[new AuthMiddleware() , new RoleMiddleware('admin')] );

$router->dispatch();
