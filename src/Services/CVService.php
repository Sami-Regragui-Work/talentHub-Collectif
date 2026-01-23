<?php

namespace App\Services;

use App\Models\Cv;
use App\Repositories\CvRepository;

class CvService
{
    private CvRepository $cvRepo;
    private string $uploadDir;
    private array $allowedExtensions = ['pdf', 'doc', 'docx'];
    private int $maxFileSize = 5242880; 

    public function __construct()
    {
        $this->cvRepo = new CvRepository();
        $this->uploadDir = __DIR__ . '/../../public/uploads/cvs';
        
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function uploadCv(array $file, int $userId): ?Cv
    {
        
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            error_log("CV upload error: Invalid file upload");
            return null;
        }

        
        if ($file['size'] > $this->maxFileSize) {
            error_log("CV upload error: File too large");
            return null;
        }

        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            error_log("CV upload error: Invalid file type");
            return null;
        }

        
        $userDir = $this->uploadDir . '/' . $userId;
        if (!is_dir($userDir)) {
            mkdir($userDir, 0755, true);
        }

        
        $filename = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $filePath = $userDir . '/' . $filename;

    
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            error_log("CV upload error: Failed to move file");
            return null;
        }

        
        $relativePath = '/uploads/cvs/' . $userId . '/' . $filename;
        $cvId = $this->cvRepo->create($relativePath, $file['name']);

        if (!$cvId) {
            
            unlink($filePath);
            return null;
        }

        return $this->cvRepo->findById($cvId);
    }

    public function getCv(int $cvId): ?Cv
    {
        return $this->cvRepo->findById($cvId);
    }

    public function getCvsByUser(int $userId): array
    {
        return $this->cvRepo->findByUserId($userId);
    }

    public function deleteCv(int $cvId): bool
    {
        $cv = $this->cvRepo->findById($cvId);
        if (!$cv) {
            return false;
        }

        
        $absolutePath = $cv->getAbsolutePath();
        if (file_exists($absolutePath)) {
            unlink($absolutePath);
        }

    
        return $this->cvRepo->delete($cvId);
    }

    public function downloadCv(int $cvId): ?array
    {
        $cv = $this->cvRepo->findById($cvId);
        if (!$cv) {
            return null;
        }

        $absolutePath = $cv->getAbsolutePath();
        if (!file_exists($absolutePath)) {
            return null;
        }

        return [
            'path' => $absolutePath,
            'filename' => $cv->getFilename(),
            'mime' => mime_content_type($absolutePath)
        ];
    }

    public function validateCvFile(array $file): array
    {
        $errors = [];

        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload failed";
            return $errors;
        }

        if ($file['size'] > $this->maxFileSize) {
            $errors[] = "File size must be less than 5MB";
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            $errors[] = "Only PDF, DOC, and DOCX files are allowed";
        }

        return $errors;
    }
}