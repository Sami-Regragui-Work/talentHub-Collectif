<?php

namespace App\Controllers;

use App\Repositories\ApplicationRepository;
use App\Repositories\JobRepository;

class ApplicationController extends BaseController
{
    private ApplicationRepository $appRepo;
    private JobRepository $jobRepo;

    public function __construct()
    {
        parent::__construct();
        $this->appRepo = new ApplicationRepository();
        $this->jobRepo = new JobRepository();
    }

    // Candidate methods
    public function index(): void
    {
        $this->requireRole('candidate');
        
        $applications = $this->appRepo->findByUser($this->currentUser->getId());
        $this->render('candidate/applications/index.twig', [
            'applications' => $applications
        ]);
    }

    public function create(int $jobId): void
    {
        $this->requireRole('candidate');
        
        $job = $this->jobRepo->findById($jobId);
        if (!$job) {
            $this->renderError(404);
        }
        
        $this->render('candidate/applications/create.twig', ['job' => $job]);
    }

    public function store(): void
    {
        $this->requireRole('candidate');
        
        try {
            $cvPath = $this->uploadFile($_FILES['cv'], 'uploads/cvs/', ['pdf'], 2097152);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/jobs');
        }

        $this->appRepo->create([
            'user_id' => $this->currentUser->getId(),
            'job_offer_id' => $_POST['job_id'],
            'cv_path' => $cvPath,
            'message' => $_POST['message'] ?? null
        ]);
        
        $this->setFlash('success', "Candidature envoyée");
        $this->redirect('/candidate/applications');
    }

    // Recruiter methods
    public function recruiterIndex(): void
    {
        $this->requireRole('recruiter');
        
        $applications = $this->appRepo->findByRecruiter($this->currentUser->getId());
        $this->render('recruiter/applications/index.twig', [
            'applications' => $applications
        ]);
    }

    public function accept(int $id): void
    {
        $this->requireRole('recruiter');
        
        $app = $this->appRepo->findById($id);
        if (!$app) {
            $this->renderError(404);
        }
        
        // Verify ownership
        $job = $this->jobRepo->findById($app->getJobOfferId());
        if ($job->getRecruiterId() !== $this->currentUser->getId()) {
            $this->renderError(403);
        }
        
        $this->appRepo->updateStatus($id, 'accepted');
        $this->setFlash('success', "Candidature acceptée");
        $this->redirect('/recruiter/applications');
    }

    public function reject(int $id): void
    {
        $this->requireRole('recruiter');
        
        $app = $this->appRepo->findById($id);
        if (!$app) {
            $this->renderError(404);
        }
        
        $job = $this->jobRepo->findById($app->getJobOfferId());
        if ($job->getRecruiterId() !== $this->currentUser->getId()) {
            $this->renderError(403);
        }
        
        $this->appRepo->updateStatus($id, 'rejected');
        $this->setFlash('success', "Candidature rejetée");
        $this->redirect('/recruiter/applications');
    }
}