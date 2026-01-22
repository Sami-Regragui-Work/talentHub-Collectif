<?php

namespace App\Controllers;

use App\Repositories\TagRepository;
use App\Services\AuthService;
use App\View;

class TagController
{
    private TagRepository $tagRepository;
    private AuthService $authService;

    public function __construct()
    {
        $this->tagRepository = new TagRepository();
        $this->authService = new AuthService();
    }

    
    public function index(): void
    {
        $user = $this->authService->getCurrentUser();
        $tags = $this->tagRepository->findAll();

        $success = $_SESSION['success'] ?? null;
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['success'], $_SESSION['errors']);

        View::render('admin/tags/index.twig', [
            'user' => $user,
            'tags' => $tags,
            'success' => $success,
            'errors' => $errors
        ]);
    }

    
    public function create(): void
    {
        $user = $this->authService->getCurrentUser();
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        View::render('admin/tags/create.twig', [
            'user' => $user,
            'errors' => $errors
        ]);
    }

    
    public function store(): void
    {
        $errors = [];

        
        if (empty($_POST['name']) || strlen(trim($_POST['name'])) < 2) {
            $errors[] = "Tag name must be at least 2 characters";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /admin/tags/create');
            exit;
        }

        
        $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');

        $success = $this->tagRepository->create([
            'name' => $name
        ]);

        if ($success) {
            $_SESSION['success'] = "Tag created successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to create tag. Tag might already exist."];
        }

        header('Location: /admin/tags');
        exit;
    }

    
    public function edit(int $id): void
    {
        $user = $this->authService->getCurrentUser();
        $tag = $this->tagRepository->findById($id);

        if (!$tag) {
            $_SESSION['errors'] = ["Tag not found"];
            header('Location: /admin/tags');
            exit;
        }

        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        View::render('admin/tags/edit.twig', [
            'user' => $user,
            'tag' => $tag,
            'errors' => $errors
        ]);
    }

    
    public function update(int $id): void
    {
        $errors = [];

        
        if (empty($_POST['name']) || strlen(trim($_POST['name'])) < 2) {
            $errors[] = "Tag name must be at least 2 characters";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /admin/tags/edit/$id");
            exit;
        }

        
        $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');

        $success = $this->tagRepository->update($id, [
            'name' => $name
        ]);

        if ($success) {
            $_SESSION['success'] = "Tag updated successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to update tag"];
        }

        header('Location: /admin/tags');
        exit;
    }

    
    public function delete(int $id): void
    {
        $success = $this->tagRepository->softDelete($id);

        if ($success) {
            $_SESSION['success'] = "Tag archived successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to archive tag"];
        }

        header('Location: /admin/tags');
        exit;
    }

    
    public function restore(int $id): void
    {
        $success = $this->tagRepository->restore($id);

        if ($success) {
            $_SESSION['success'] = "Tag restored successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to restore tag"];
        }

        header('Location: /admin/tags');
        exit;
    }
}