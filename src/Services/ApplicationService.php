<?php

namespace App\Services;

use App\Models\Application;
use App\Repositories\ApplicationRepository;
use App\Repositories\JobRepository;
use App\Repositories\UserRepository;
use App\Repositories\CvRepository;

class ApplicationService
{
    private ApplicationRepository $applicationRepo;
    private JobRepository $jobRepo;
    private UserRepository $userRepo;
    private CvRepository $cvRepo;

    public function __construct()
    {
        $this->applicationRepo = new ApplicationRepository();
        $this->jobRepo = new JobRepository();
        $this->userRepo = new UserRepository();
        $this->cvRepo = new CvRepository();
    }

    public function apply(int $userId, int $jobOfferId, ?int $cvId = null): ?Application
    {
        
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return null;
        }


        $job = $this->jobRepo->findById($jobOfferId);
        if (!$job || !$job->isActive()) {
            return null;
        }

        
        if ($this->applicationRepo->hasApplied($userId, $jobOfferId)) {
            return null;
        }

        
        $applicationId = $this->applicationRepo->create($userId, $jobOfferId, $cvId);
        
        if (!$applicationId) {
            return null;
        }

        return $this->applicationRepo->findById($applicationId);
    }

    public function acceptApplication(int $applicationId): bool
    {
        $application = $this->applicationRepo->findById($applicationId);
        if (!$application) {
            return false;
        }

        $application->accept();
        return $this->applicationRepo->updateStatus($applicationId, 'accepted');
    }

    public function rejectApplication(int $applicationId): bool
    {
        $application = $this->applicationRepo->findById($applicationId);
        if (!$application) {
            return false;
        }

        $application->reject();
        return $this->applicationRepo->updateStatus($applicationId, 'rejected');
    }

    public function getApplicationsByCandidate(int $userId): array
    {
        return $this->applicationRepo->findByUserId($userId);
    }

    public function getApplicationsByJob(int $jobOfferId): array
    {
        return $this->applicationRepo->findByJobOfferId($jobOfferId);
    }

    public function getApplicationsByRecruiter(int $recruiterId): array
    {
        return $this->applicationRepo->findByRecruiterId($recruiterId);
    }

    public function getPendingApplications(int $recruiterId): array
    {
        return $this->applicationRepo->findPendingByRecruiterId($recruiterId);
    }

    public function getApplicationWithDetails(int $applicationId): ?Application
    {
        return $this->applicationRepo->findById($applicationId);
    }

    public function hasApplied(int $userId, int $jobOfferId): bool
    {
        return $this->applicationRepo->hasApplied($userId, $jobOfferId);
    }

    public function withdrawApplication(int $applicationId, int $userId): bool
    {
        $application = $this->applicationRepo->findById($applicationId);
        
        
        if (!$application || $application->getCandidate()->getId() !== $userId) {
            return false;
        }

        
        if (!$application->isPending()) {
            return false;
        }

        return $this->applicationRepo->delete($applicationId);
    }
}