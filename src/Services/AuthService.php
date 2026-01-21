<?php

namespace App\Services;

use App\enumTypes\RoleName;
use App\Models\User;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $user_repo;

    public function __construct()
    {
        $this->user_repo = new UserRepository();

        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function register(string $fullname, string $email, string $password, string $role): bool
    {
        if ($this->user_repo->emailExists($email)) return false;

        $role_name = RoleName::from($role);
        $this->user_repo->create($fullname, $email, $password, $role_name);

        return true;
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->user_repo->findByEmail($email);

        if (!$user) return false;
        if (!password_verify($password, $user->getPassword())) return false;

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_role'] = $user->getRole()->getName()->value;
        $_SESSION['user_name'] = $user->getFullName();

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

        return $this->user_repo->findById($_SESSION['user_id']);
    }

    public function getCurrentRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }
}
