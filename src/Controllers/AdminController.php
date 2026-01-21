<?php

namespace App\Controllers;

use App\Interfaces\DashboardInterface;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\View;

class AdminController implements DashboardInterface
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function dashboard(): void
    {
        $user = $this->authService->getCurrentUser();
        $user_repo = new UserRepository();

        $users = $user_repo->findAll();
        $candidates = $user_repo->findByRoleName("candidate");
        $recruiters = $user_repo->findByRoleName("recruiter");
        View::render('admin/dashboard.twig', [
            'user' => $user,
            'users' => count($users),
            'candidates' => count($candidates),
            'recruiters' => count($recruiters),
        ]);
    }
}
