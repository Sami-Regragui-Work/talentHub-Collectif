<?php

namespace App\Services;

use App\Models\Recruiter;
use App\Repositories\RecruiterRepository;
use App\Repositories\UserRepository;

class RecruiterService
{
    private RecruiterRepository $recruiterRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->recruiterRepo = new RecruiterRepository();
        $this->userRepo = new UserRepository();
    }

    public function getRecruiterByUserId(int $userId): ?Recruiter
    {
        return $this->recruiterRepo->findById($userId);
    }

    public function updateCompanyName(int $userId, string $companyName): bool
    {
        $recruiter = $this->recruiterRepo->findById($userId);
        if (!$recruiter) {
            return false;
        }

        return $this->recruiterRepo->updateCompanyName($userId, $companyName);
    }

    public function getAllRecruiters(): array
    {
        return $this->recruiterRepo->findAll();
    }

    public function getActiveRecruiters(): array
    {
        return $this->recruiterRepo->findActiveRecruiters();
    }

    public function getRecruiterStats(int $userId): array
    {
        $recruiter = $this->recruiterRepo->findById($userId);
        if (!$recruiter) {
            return [];
        }

        return [
            'total_jobs' => $this->recruiterRepo->getJobCount($userId),
            'active_jobs' => $this->recruiterRepo->getActiveJobCount($userId),
            'total_applications' => $this->recruiterRepo->getApplicationCount($userId),
            'pending_applications' => $this->recruiterRepo->getPendingApplicationCount($userId),
        ];
    }

    public function canPublishJobs(int $userId): bool
    {
        $recruiter = $this->recruiterRepo->findById($userId);
        return $recruiter && $recruiter->canPublishJobOffers();
    }
}