<?php

namespace App\Services;

use App\enumTypes\RoleName;
use App\Models\User;
use App\Repositories\RecruiterRepository;
use App\Repositories\UserRepository;
use PDO;

class AuthService
{
    private UserRepository $user_repo;
    private RecruiterRepository $recruiter_repo;

    public function __construct()
    {
        $this->user_repo = new UserRepository();
        $this->recruiter_repo = new RecruiterRepository();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register(array $data): bool
    {
        if ($this->user_repo->emailExists($data["email"])) {
            return false;
        }

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_name' => $data['role_name']
        ];

        $user = $this->user_repo->create($userData, [
            PDO::PARAM_STR,
            PDO::PARAM_STR,
            PDO::PARAM_STR,
            PDO::PARAM_STR
        ]);

        if (!$user) {
            return false;
        }

        if ($data['role_name'] === 'recruiter' && isset($data['company_name'])) {
            $recruiterData = [
                'id' => $user->getId(),
                'company_name' => $data['company_name']
            ];
            $this->recruiter_repo->create($recruiterData, [PDO::PARAM_INT, PDO::PARAM_STR]);
        }

        return true;
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->user_repo->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user->getPassword())) {
            return false;
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_role'] = $user->getRole()->getStringName();
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

        return $this->user_repo->findById($_SESSION['user_id']);
    }

    public function getCurrentRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }
}
