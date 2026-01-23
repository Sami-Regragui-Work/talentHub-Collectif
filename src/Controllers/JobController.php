<?php

namespace App\Controllers;

use App\Repositories\JobRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\TagRepository;
use App\Services\AuthService;
use App\View;
use PDO;

class JobController
{
    private JobRepository $jobRepository;
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;
    private AuthService $authService;

    public function __construct()
    {
        $this->jobRepository = new JobRepository();
        $this->categoryRepository = new CategoryRepository();
        $this->tagRepository = new TagRepository();
        $this->authService = new AuthService();
    }

    
    public function index(): void
    {
        $user = $this->authService->getCurrentUser();
        $jobs = $this->jobRepository->findActive();
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();

        View::render('jobs/index.twig', [
            'user' => $user,
            'jobs' => $jobs,
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

    
    public function show(int $id): void
    {
        $user = $this->authService->getCurrentUser();
        $job = $this->jobRepository->findById($id);

        if (!$job || $job['deleted_at'] !== null) {
            $_SESSION['errors'] = ["Job not found"];
            header('Location: /jobs');
            exit;
        }

        View::render('jobs/show.twig', [
            'user' => $user,
            'job' => $job
        ]);
    }

    
    public function recruiterJobs(): void
    {
        $user = $this->authService->getCurrentUser();
        $jobs = $this->jobRepository->findByRecruiterId($user['id']);

        $success = $_SESSION['success'] ?? null;
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['success'], $_SESSION['errors']);

        View::render('recruiter/jobs/index.twig', [
            'user' => $user,
            'jobs' => $jobs,
            'success' => $success,
            'errors' => $errors
        ]);
    }

    
    public function create(): void
    {
        $user = $this->authService->getCurrentUser();
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();

        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        View::render('recruiter/jobs/create.twig', [
            'user' => $user,
            'categories' => $categories,
            'tags' => $tags,
            'errors' => $errors
        ]);
    }

    
    public function store(): void
    {
        $errors = [];
        $user = $this->authService->getCurrentUser();

        if (empty($_POST['title']) || strlen(trim($_POST['title'])) < 5) {
            $errors[] = "Job title must be at least 5 characters";
        }
        if (empty($_POST['description']) || strlen(trim($_POST['description'])) < 20) {
            $errors[] = "Job description must be at least 20 characters";
        }
        if (empty($_POST['category_name'])) {
            $errors[] = "Category is required";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /recruiter/jobs/create');
            exit;
        }

        $jobData = [
            'title' => htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8'),
            'salary' => !empty($_POST['salary']) ? (float)$_POST['salary'] : null,
            'category_name' => htmlspecialchars(trim($_POST['category_name']), ENT_QUOTES, 'UTF-8'),
            'recruiter_id' => $user->getId()
        ];

        $job = $this->jobRepository->create($jobData, [
            PDO::PARAM_STR,
            PDO::PARAM_STR,
            isset($_POST['salary']) ? PDO::PARAM_STR : PDO::PARAM_NULL,
            PDO::PARAM_STR,
            PDO::PARAM_INT
        ]);

        if ($job) {
            $selectedTags = $_POST['tags'] ?? [];
            if (!empty($selectedTags)) {
                $this->jobRepository->attachTags($job->getId(), $selectedTags);
            }
            $_SESSION['success'] = "Job created successfully!";
            header('Location: /recruiter/jobs');
        } else {
            $_SESSION['errors'] = ["Failed to create job"];
            header('Location: /recruiter/jobs/create');
        }
        exit;
    }


    public function edit(int $id): void
    {
        $user = $this->authService->getCurrentUser();
        $job = $this->jobRepository->findById($id);

        if (!$job || $job->getRecruiter()->getId() !== $user->getId()) {
            $_SESSION['errors'] = ["Job not found or unauthorized"];
            header('Location: /recruiter/jobs');
            exit;
        }

        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        View::render('recruiter/jobs/edit.twig', [
            'user' => $user,
            'job' => $job,
            'categories' => $categories,
            'tags' => $tags,
            'errors' => $errors
        ]);
    }


    public function update(int $id): void
    {
        $errors = [];
        $user = $this->authService->getCurrentUser();

        $job = $this->jobRepository->findById($id);
        if (!$job || $job->getRecruiter()->getId() !== $user->getId()) {
            $_SESSION['errors'] = ["Job not found or unauthorized"];
            header('Location: /recruiter/jobs');
            exit;
        }

        if (empty($_POST['title']) || strlen(trim($_POST['title'])) < 5) {
            $errors[] = "Job title must be at least 5 characters";
        }
        if (empty($_POST['description']) || strlen(trim($_POST['description'])) < 20) {
            $errors[] = "Job description must be at least 20 characters";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /recruiter/jobs/edit/$id");
            exit;
        }

        $jobData = [
            'title' => htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8'),
            'salary' => !empty($_POST['salary']) ? (float)$_POST['salary'] : null,
            'category_name' => htmlspecialchars(trim($_POST['category_name']), ENT_QUOTES, 'UTF-8')
        ];

        $success = $this->jobRepository->update($id, $jobData, [
            PDO::PARAM_STR,
            PDO::PARAM_STR,
            isset($_POST['salary']) ? PDO::PARAM_STR : PDO::PARAM_NULL,
            PDO::PARAM_STR
        ]);

        if ($success) {
            $selectedTags = $_POST['tags'] ?? [];
            $this->jobRepository->syncTags($id, $selectedTags);
            $_SESSION['success'] = "Job updated successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to update job"];
        }

        header('Location: /recruiter/jobs');
        exit;
    }


    public function delete(int $id): void
    {
        $user = $this->authService->getCurrentUser();
        $job = $this->jobRepository->findById($id);

        if (!$job || $job->getRecruiter()->getId() !== $user->getId()) {
            $_SESSION['errors'] = ["Job not found or unauthorized"];
            header('Location: /recruiter/jobs');
            exit;
        }

        $success = $this->jobRepository->archive($id);

        if ($success) {
            $_SESSION['success'] = "Job archived successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to archive job"];
        }

        header('Location: /recruiter/jobs');
        exit;
    }

    
    public function adminArchive(int $id): void
    {
        $success = $this->jobRepository->softDelete($id);

        if ($success) {
            $_SESSION['success'] = "Job archived successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to archive job"];
        }

        header('Location: /admin/jobs');
        exit;
    }

    
    public function restore(int $id): void
    {
        $success = $this->jobRepository->restore($id);

        if ($success) {
            $_SESSION['success'] = "Job restored successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to restore job"];
        }

        header('Location: /admin/jobs');
        exit;
    }

    
    // public function recommended(): void
    // {
    //     $user = $this->authService->getCurrentUser();
        
        
    //     $userSkills = $this->jobRepository->getUserSkills($user['id']);
    //     $salaryExpectation = $user['salary_expectation'] ?? null;

    //     $recommendedJobs = $this->jobRepository->findRecommendedJobs(
    //         $userSkills,
    //         $salaryExpectation
    //     );

    //     View::render('candidate/recommended-jobs.twig', [
    //         'user' => $user,
    //         'jobs' => $recommendedJobs
    //     ]);
    // }
}