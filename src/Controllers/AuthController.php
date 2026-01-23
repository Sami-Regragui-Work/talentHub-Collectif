<?php

namespace App\Controllers;

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirectToDashboard();
        }
        
        $this->render('auth/login.twig', [
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success')
        ]);
    }

    public function login(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirectToDashboard();
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $this->setFlash('error', "Email et mot de passe requis");
            $this->redirect('/login');
        }
        
        if ($this->auth->login($email, $password)) {
            $this->redirectToDashboard();
        } else {
            $this->setFlash('error', "Email ou mot de passe incorrect");
            $this->redirect('/login');
        }
    }

    public function showRegister(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirectToDashboard();
        }
        
        $this->render('auth/register.twig', [
            'error' => $this->getFlash('error')
        ]);
    }

    public function register(): void
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirectToDashboard();
        }
        
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'candidate';
        $companyName = $_POST['company_name'] ?? null;
        
        if (empty($fullname) || empty($email) || empty($password)) {
            $this->setFlash('error', "Tous les champs sont requis");
            $this->redirect('/register');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', "Email invalide");
            $this->redirect('/register');
        }
        
        if (strlen($password) < 6) {
            $this->setFlash('error', "Le mot de passe doit faire au moins 6 caractères");
            $this->redirect('/register');
        }
        
        $success = $this->auth->register($fullname, $email, $password, $role, $companyName);
        
        if (!$success) {
            $this->setFlash('error', "Cet email est déjà utilisé");
            $this->redirect('/register');
        }
        
        $this->setFlash('success', "Inscription réussie ! Connectez-vous.");
        $this->redirect('/login');
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->redirect('/login');
    }
}