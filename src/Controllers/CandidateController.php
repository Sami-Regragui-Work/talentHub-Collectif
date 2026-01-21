<?php

namespace App\Controllers;

use App\Interfaces\DashboardInterface;
use App\Services\AuthService;
use App\View;

class CandidateController implements DashboardInterface
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function dashboard(): void
    {
        $user = $this->authService->getCurrentUser();
        View::render('candidate/dashboard.twig', ['user' => $user]);
    }
}
