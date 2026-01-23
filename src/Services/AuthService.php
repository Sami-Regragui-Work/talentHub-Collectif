<?php

namespace App\Services;

use App\enumTypes\RoleName;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\RecruiterRepository;

class AuthService
{
    private UserRepository $userRepo;
    private RecruiterRepository $recruiterRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
        $this->recruiterRepo = new RecruiterRepository();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register(string $name, string $email, string $password, string $role, ?string $companyName = null): bool
    {
        if ($this->userRepo->emailExists($email)) {
            return false;
        }

        $roleName = RoleName::from($role);
        $user = $this->userRepo->create($name, $email, $password, $roleName);

        if ($roleName === RoleName::RECRUITER && $companyName) {
            $this->recruiterRepo->create($user->getId(), $companyName);
        }

        return true;
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            return false;
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_role'] = $user->getRole()->getName()->value;
        $_SESSION['user_name'] = $user->getName();

        return true;
    }

    public function logout(): void
    {
        session_destroy();
        session_start();
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return $this->userRepo->findById($_SESSION['user_id']);
    }

    public function getCurrentRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function requireRole(RoleName $requiredRole): void
    {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        if (!$user || $user->getRole()->getName() !== $requiredRole) {
            http_response_code(403);
            header('Location: /403');
            exit;
        }
    }
}