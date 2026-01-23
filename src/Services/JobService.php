<?php

namespace App\Services;

use App\Models\JobOffer;
use App\Repositories\JobRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\RecruiterRepository;
use App\Repositories\TagRepository;

class JobService
{
    private JobRepository $jobRepo;
    private CategoryRepository $categoryRepo;
    private RecruiterRepository $recruiterRepo;
    private TagRepository $tagRepo;

    public function __construct()
    {
        $this->jobRepo = new JobRepository();
        $this->categoryRepo = new CategoryRepository();
        $this->recruiterRepo = new RecruiterRepository();
        $this->tagRepo = new TagRepository();
    }

    public function createJob(
        string $title,
        string $description,
        ?float $salary,
        string $categoryName,
        int $recruiterId,
        array $tagNames = []
    ): ?JobOffer {
        
        $category = $this->categoryRepo->findByName($categoryName);
        if (!$category) {
            return null;
        }

        
        $recruiter = $this->recruiterRepo->findById($recruiterId);
        if (!$recruiter) {
            return null;
        }

        
        $jobId = $this->jobRepo->create($title, $description, $salary, $categoryName, $recruiterId);
        
        if (!$jobId) {
            return null;
        }

        
        if (!empty($tagNames)) {
            $this->jobRepo->attachTags($jobId, $tagNames);
        }

        return $this->jobRepo->findById($jobId);
    }

    public function updateJob(
        int $jobId,
        string $title,
        string $description,
        ?float $salary,
        string $categoryName,
        array $tagNames = []
    ): bool {
        
        $category = $this->categoryRepo->findByName($categoryName);
        if (!$category) {
            return false;
        }

        $success = $this->jobRepo->update($jobId, $title, $description, $salary, $categoryName);
        
        if ($success) {
            
            $this->jobRepo->syncTags($jobId, $tagNames);
        }

        return $success;
    }

    public function archiveJob(int $jobId): bool
    {
        return $this->jobRepo->archive($jobId);
    }

    public function restoreJob(int $jobId): bool
    {
        return $this->jobRepo->restore($jobId);
    }

    public function deleteJob(int $jobId): bool
    {
        return $this->jobRepo->delete($jobId);
    }

    public function getActiveJobs(): array
    {
        return $this->jobRepo->findActive();
    }

    public function getArchivedJobs(): array
    {
        return $this->jobRepo->findArchived();
    }

    public function getJobsByRecruiter(int $recruiterId): array
    {
        return $this->jobRepo->findByRecruiterId($recruiterId);
    }

    public function getJobsByCategory(string $categoryName): array
    {
        return $this->jobRepo->findByCategory($categoryName);
    }

    public function searchJobs(?string $keyword = null, ?string $categoryName = null, array $tagNames = []): array
    {
        return $this->jobRepo->search($keyword, $categoryName, $tagNames);
    }

    public function getJobWithDetails(int $jobId): ?JobOffer
    {
        return $this->jobRepo->findById($jobId);
    }
}