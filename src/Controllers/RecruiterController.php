<?php

namespace App\Controllers;

use App\Interfaces\DashboardInterface;
use App\Repositories\JobRepository;
use App\Repositories\ApplicationRepository;

class RecruiterController extends BaseController implements DashboardInterface
{
    private JobRepository $jobRepo;
    private ApplicationRepository $appRepo;

    public function __construct()
    {
        parent::__construct();
        $this->jobRepo = new JobRepository();
        $this->appRepo = new ApplicationRepository();
    }

    public function dashboard(): void
    {
        $this->requireRole('recruiter');
        
        $recruiterId = $this->currentUser->getId();
        
        $stats = [
            'myOffers' => count($this->jobRepo->findByRecruiter($recruiterId)),
            'myApplications' => count($this->appRepo->findByRecruiter($recruiterId))
        ];
        
        $this->render('recruiter/dashboard.twig', ['stats' => $stats]);
    }
}