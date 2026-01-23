<?php

namespace App\Controllers;

use App\Interfaces\DashboardInterface;

class CandidateController extends BaseController implements DashboardInterface
{
    public function dashboard(): void
    {
        $this->requireRole('candidate');
        $this->render('candidate/dashboard.twig');
    }
}