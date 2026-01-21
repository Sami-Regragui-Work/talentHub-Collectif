<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\CandidateController;
use App\Controllers\RecruiterController;
use App\Controllers\AdminController;

session_start();

$router = new Router();

$router->addRoute('GET', '/', AuthController::class, 'showLogin');
$router->addRoute('GET', '/register', AuthController::class, 'showRegister');
$router->addRoute('POST', '/register', AuthController::class, 'register');
$router->addRoute('GET', '/login', AuthController::class, 'showLogin');
$router->addRoute('POST', '/login', AuthController::class, 'login');
$router->addRoute('GET', '/logout', AuthController::class, 'logout');

$router->addRoute('GET', '/candidate/dashboard', CandidateController::class, 'dashboard', 'candidate');

$router->addRoute('GET', '/recruiter/dashboard', RecruiterController::class, 'dashboard', 'recruiter');

$router->addRoute('GET', '/admin/dashboard', AdminController::class, 'dashboard', 'admin');

$router->dispatch();
