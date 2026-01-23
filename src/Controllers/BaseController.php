<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\View;
use App\Models\User;

abstract class BaseController
{
    protected AuthService $auth;
    protected ?User $currentUser = null;
    
    public function __construct()
    {
        $this->auth = new AuthService();
        $this->currentUser = $this->auth->getCurrentUser();
    }
    
    /**
     * Check if user is authenticated, redirect if not
     */
    protected function requireAuth(): void
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
        }
    }
    
    /**
     * Check if user has specific role, render 403 if not
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        
        if ($this->auth->getCurrentRole() !== $role) {
            $this->renderError(403);
        }
    }
    
    /**
     * Check if user has one of the allowed roles
     */
    protected function requireRoles(array $roles): void
    {
        $this->requireAuth();
        
        if (!in_array($this->auth->getCurrentRole(), $roles)) {
            $this->renderError(403);
        }
    }
    
    /**
     * Check if user owns a resource or is admin
     */
    protected function requireOwnerOrAdmin(int $resourceOwnerId): void
    {
        $this->requireAuth();
        
        $isAdmin = $this->auth->getCurrentRole() === 'admin';
        $isOwner = $this->currentUser && $this->currentUser->getId() === $resourceOwnerId;
        
        if (!$isAdmin && !$isOwner) {
            $this->renderError(403);
        }
    }
    
    /**
     * Render a view with automatic user injection
     */
    protected function render(string $template, array $data = [], int $code = 200): void
    {
        // Always inject current user and auth status
        $data['user'] = $this->currentUser;
        $data['isLoggedIn'] = $this->auth->isLoggedIn();
        $data['userRole'] = $this->auth->getCurrentRole();
        
        View::render($template, $data, $code);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
    
    /**
     * Redirect to role-specific dashboard
     */
    protected function redirectToDashboard(): void
    {
        $role = $this->auth->getCurrentRole();
        $this->redirect("/{$role}/dashboard");
    }
    
    /**
     * Render error pages
     */
    protected function renderError(int $code): void
    {
        $templates = [
            403 => 'errors/403.twig',
            404 => 'errors/404.twig'
        ];
        
        $template = $templates[$code] ?? 'errors/404.twig';
        $this->render($template, [], $code);
        exit;
    }
    
    /**
     * Set flash message in session
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION[$type] = $message;
    }
    
    /**
     * Get and clear flash message
     */
    protected function getFlash(string $type): ?string
    {
        $message = $_SESSION[$type] ?? null;
        unset($_SESSION[$type]);
        return $message;
    }
    
    /**
     * Handle file upload with validation
     */
    protected function uploadFile(array $file, string $directory, array $allowedTypes = ['pdf'], int $maxSize = 2097152): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Validate file type
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            throw new \Exception("Type de fichier non autorisé");
        }
        
        // Validate size (default 2MB)
        if ($file['size'] > $maxSize) {
            throw new \Exception("Fichier trop volumineux");
        }
        
        $uploadDir = __DIR__ . '/../../public/' . $directory;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $filename = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception("Erreur lors de l'upload");
        }
        
        return '/' . $directory . $filename;
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $this->renderError(403);
        }
    }
    
    /**
     * Generate CSRF token
     */
    protected function generateCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}