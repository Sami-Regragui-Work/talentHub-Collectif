<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\View;

class AuthController
{
    private AuthService $auth_service;

    public function __construct()
    {
        $this->auth_service = new AuthService();
    }

    private function redirectToDashboard(): void
    {
        $role = $this->auth_service->getCurrentRole();
        header("Location: /{$role}/dashboard");
        exit;
    }

    public function showRegister(): void
    {
        if ($this->auth_service->isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }

        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        View::render('auth/register.twig', ['errors' => $errors]);
    }

    public function register(): void
    {
        $errors = [];

        if (empty($_POST['name'])) {
            $errors[] = "name is required";
        }
        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }
        if (empty($_POST['password']) || strlen($_POST['password']) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        if (empty($_POST['role']) || !in_array($_POST['role'], ['candidate', 'recruiter'])) {
            $errors[] = "Valid role is required";
        }

        if ($_POST['role'] === 'recruiter' && empty($_POST['company_name'])) {
            $errors[] = "Company name is required for recruiters";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /register');
            exit;
        }

        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'role_name' => $_POST['role']
        ];

        if ($_POST['role'] === 'recruiter') {
            $data['company_name'] = $_POST['company_name'];
        }

        $success = $this->auth_service->register($data);

        if (!$success) {
            $_SESSION['errors'] = ["Email already exists"];
            header('Location: /register');
            exit;
        }

        $_SESSION['success'] = "Registration successful! Please login.";
        header('Location: /login');
        exit;
    }

    public function showLogin(): void
    {
        if ($this->auth_service->isLoggedIn()) {
            $this->redirectToDashboard();
            return;
        }

        $errors = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['success']);

        View::render('auth/login.twig', [
            'errors' => $errors,
            'success' => $success
        ]);
    }

    public function login(): void
    {
        $errors = [];

        if (empty($_POST['email']) || empty($_POST['password'])) {
            $errors[] = "Email and password are required";
            $_SESSION['errors'] = $errors;
            header('Location: /login');
            exit;
        }

        $success = $this->auth_service->login($_POST['email'], $_POST['password']);

        if (!$success) {
            $_SESSION['errors'] = ["Invalid email or password"];
            header('Location: /login');
            exit;
        }

        $this->redirectToDashboard();
    }

    public function logout(): void
    {
        $this->auth_service->logout();
        header('Location: /login');
        exit;
    }
}
