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

        if (empty($_POST['fullname'])) {
            $errors[] = "Full name is required";
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

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /register');
            exit;
        }

        $success = $this->auth_service->register(
            $_POST['name'],
            $_POST['email'],
            $_POST['password'],
            $_POST['role']
        );

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
