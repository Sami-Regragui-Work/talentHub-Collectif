<?php

namespace App\Services;

use App\enumTypes\RoleName;
use App\Models\User;
use App\Repositories\UserRepository;
use PDO;

class AuthService
{
    private UserRepository $user_repo;

    public function __construct()
    {
        $this->user_repo = new UserRepository();

        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function register(array $data): bool
    {
        // string $fullname, string $email, string $password, string $role
        if ($this->user_repo->emailExists($data["email"])) return false;

        $data["role_name"] = RoleName::from($data["role_name"]);
        $this->user_repo->create($data,[PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR]);

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
