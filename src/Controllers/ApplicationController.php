<?php

namespace App\Controllers;

use App\Repositories\ApplicationRepository;
use App\Repositories\JobRepository;
use App\Services\AuthService;
use App\View;

class ApplicationController
{
    private ApplicationRepository $applicationRepository;
    private JobRepository $jobRepository;
    private AuthService $authService;

    public function __construct()
    {
        $this->applicationRepository = new ApplicationRepository();
        $this->jobRepository = new JobRepository();
        $this->authService = new AuthService();
    }


    public function create(int $jobId): void
    {
        $user = $this->authService->getCurrentUser();
        $job = $this->jobRepository->findById($jobId);

        if (!$job || $job->getIsArchived()) {
            $_SESSION['errors'] = ["Job not found"];
            header('Location: /jobs');
            exit;
        }

        if ($this->applicationRepository->hasUserApplied($user->getId(), $jobId)) {
            $_SESSION['errors'] = ["You have already applied to this job"];
            header("Location: /jobs/$jobId");
            exit;
        }

        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        View::render('candidate/applications/create.twig', [
            'user' => $user,
            'job' => $job,
            'errors' => $errors
        ]);
    }


    public function store(int $jobId): void
    {
        $errors = [];
        $user = $this->authService->getCurrentUser();


        if (empty($_POST['cover_letter']) || strlen(trim($_POST['cover_letter'])) < 50) {
            $errors[] = "Cover letter must be at least 50 characters";
        }


        $cvPath = null;
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvPath = $this->uploadCV($_FILES['cv'], $errors);
        } else {
            $errors[] = "CV file is required";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /jobs/$jobId/apply");
            exit;
        }


        $applicationData = [
            'user_id' => $user['id'],
            'job_id' => $jobId,
            'cover_letter' => htmlspecialchars(trim($_POST['cover_letter']), ENT_QUOTES, 'UTF-8'),
            'cv_path' => $cvPath,
            'status' => 'pending'
        ];

        $success = $this->applicationRepository->create($applicationData);

        if ($success) {
            $_SESSION['success'] = "Application submitted successfully!";
            header('Location: /candidate/applications');
        } else {
            $_SESSION['errors'] = ["Failed to submit application"];
            header("Location: /jobs/$jobId/apply");
        }
        exit;
    }


    private function uploadCV(array $file, array &$errors): ?string
    {
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $maxSize = 10 * 1024 * 1024; // 10MB


        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        unset($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = "CV must be a PDF or Word document";
            return null;
        }


        if ($file['size'] > $maxSize) {
            $errors[] = "CV file size must not exceed 10MB";
            return null;
        }


        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cv_', true) . '.' . $extension;


        $uploadDir = __DIR__ . '/../../storage/cvs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return 'storage/cvs/' . $filename;
        }

        $errors[] = "Failed to upload CV";
        return null;
    }


    public function candidateApplications(): void
    {
        $user = $this->authService->getCurrentUser();
        $applications = $this->applicationRepository->findByUserId($user['id']);

        $success = $_SESSION['success'] ?? null;
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['success'], $_SESSION['errors']);

        View::render('candidate/applications/index.twig', [
            'user' => $user,
            'applications' => $applications,
            'success' => $success,
            'errors' => $errors
        ]);
    }


    public function jobApplications(int $jobId): void
    {
        $user = $this->authService->getCurrentUser();
        $job = $this->jobRepository->findById($jobId);

        if (!$job || $job->getRecruiter()->getId() !== $user->getId()) {
            $_SESSION['errors'] = ["Job not found or unauthorized"];
            header('Location: /recruiter/jobs');
            exit;
        }

        $applications = $this->applicationRepository->findByJobOfferId($jobId);

        $success = $_SESSION['success'] ?? null;
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['success'], $_SESSION['errors']);

        View::render('recruiter/applications/index.twig', [
            'user' => $user,
            'job' => $job,
            'applications' => $applications,
            'success' => $success,
            'errors' => $errors
        ]);
    }


    public function show(int $id): void
    {
        $user = $this->authService->getCurrentUser();
        $application = $this->applicationRepository->findById($id);

        if (!$application) {
            $_SESSION['errors'] = ["Application not found"];
            header('Location: /recruiter/jobs');
            exit;
        }

        if ($application->getJob()->getRecruiter()->getId() !== $user->getId()) {
            $_SESSION['errors'] = ["Unauthorized access"];
            header('Location: /recruiter/jobs');
            exit;
        }

        View::render('recruiter/applications/show.twig', [
            'user' => $user,
            'application' => $application
        ]);
    }


    public function accept(int $id): void
    {
        $user = $this->authService->getCurrentUser();
        $application = $this->applicationRepository->findById($id);

        if (!$application) {
            $_SESSION['errors'] = ["Application not found"];
            header('Location: /recruiter/jobs');
            exit;
        }

        if ($application->getJob()->getRecruiter()->getId() !== $user->getId()) {
            $_SESSION['errors'] = ["Unauthorized"];
            header('Location: /recruiter/jobs');
            exit;
        }

        $success = $this->applicationRepository->updateStatus($id, 'accepted');

        if ($success) {
            $_SESSION['success'] = "Application accepted!";
        } else {
            $_SESSION['errors'] = ["Failed to accept application"];
        }

        header("Location: /recruiter/jobs/{$application->getJob()->getId()}/applications");
        exit;
    }


    public function reject(int $id): void
    {
        $user = $this->authService->getCurrentUser();
        $application = $this->applicationRepository->findById($id);

        if (!$application) {
            $_SESSION['errors'] = ["Application not found"];
            header('Location: /recruiter/jobs');
            exit;
        }

        if ($application->getJob()->getRecruiter()->getId() !== $user->getId()) {
            $_SESSION['errors'] = ["Unauthorized"];
            header('Location: /recruiter/jobs');
            exit;
        }

        $success = $this->applicationRepository->updateStatus($id, 'rejected');

        if ($success) {
            $_SESSION['success'] = "Application rejected!";
        } else {
            $_SESSION['errors'] = ["Failed to reject application"];
        }

        header("Location: /recruiter/jobs/{$application->getJob()->getId()}/applications");
        exit;
    }


    public function downloadCV(int $id): void
    {
        $user = $this->authService->getCurrentUser();
        $application = $this->applicationRepository->findById($id);

        if (!$application) {
            $_SESSION['errors'] = ["Application not found"];
            header('Location: /recruiter/jobs');
            exit;
        }

        if ($application->getJob()->getRecruiter()->getId() !== $user->getId()) {
            $_SESSION['errors'] = ["Unauthorized"];
            header('Location: /recruiter/jobs');
            exit;
        }

        $cv = $application->getCv();
        if (!$cv) {
            $_SESSION['errors'] = ["CV not found"];
            header("Location: /recruiter/jobs/{$application->getJob()->getId()}/applications");
            exit;
        }

        $cvPath = __DIR__ . '/../../' . $cv->getPath();

        if (!file_exists($cvPath)) {
            $_SESSION['errors'] = ["CV file not found"];
            header("Location: /recruiter/jobs/{$application->getJob()->getId()}/applications");
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($cvPath) . '"');
        header('Content-Length: ' . filesize($cvPath));
        readfile($cvPath);
        exit;
    }
}
