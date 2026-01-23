<?php

namespace App\Controllers;

use App\Repositories\CategoryRepository;
use App\Services\AuthService;
use App\View;

class CategoryController
{
    private CategoryRepository $categoryRepository;
    private AuthService $authService;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
        $this->authService = new AuthService();
    }

    public function index(): void
    {
        $user = $this->authService->getCurrentUser();
        $categories = $this->categoryRepository->findAll();

        $success = $_SESSION['success'] ?? null;
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['success'], $_SESSION['errors']);

        View::render('admin/categories/index.twig', [
            'user' => $user,
            'categories' => $categories,
            'success' => $success,
            'errors' => $errors
        ]);
    }

    
    public function create(): void
    {
        $user = $this->authService->getCurrentUser();
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        View::render('admin/categories/create.twig', [
            'user' => $user,
            'errors' => $errors
        ]);
    }

    
    public function store(): void
    {
        $errors = [];

        
        if (empty($_POST['name']) || strlen(trim($_POST['name'])) < 2) {
            $errors[] = "Category name must be at least 2 characters";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /admin/categories/create');
            exit;
        }

        
        $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
        $description = !empty($_POST['description']) 
            ? htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8') 
            : null;

        $success = $this->categoryRepository->create([
            'name' => $name,
            'description' => $description
        ]);

        if ($success) {
            $_SESSION['success'] = "Category created successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to create category"];
        }

        header('Location: /admin/categories');
        exit;
    }

    
    public function edit(int $id): void
    {
        $user = $this->authService->getCurrentUser();
        $category = $this->categoryRepository->findById($id);

        if (!$category) {
            $_SESSION['errors'] = ["Category not found"];
            header('Location: /admin/categories');
            exit;
        }

        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        View::render('admin/categories/edit.twig', [
            'user' => $user,
            'category' => $category,
            'errors' => $errors
        ]);
    }

    
    public function update(int $id): void
    {
        $errors = [];

        
        if (empty($_POST['name']) || strlen(trim($_POST['name'])) < 2) {
            $errors[] = "Category name must be at least 2 characters";
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /admin/categories/edit/$id");
            exit;
        }

        
        $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
        $description = !empty($_POST['description']) 
            ? htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8') 
            : null;

        $success = $this->categoryRepository->update($id, [
            'name' => $name,
            'description' => $description
        ]);

        if ($success) {
            $_SESSION['success'] = "Category updated successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to update category"];
        }

        header('Location: /admin/categories');
        exit;
    }

    
    public function delete(int $id): void
    {
        $success = $this->categoryRepository->softDelete($id);

        if ($success) {
            $_SESSION['success'] = "Category archived successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to archive category"];
        }

        header('Location: /admin/categories');
        exit;
    }

    
    public function restore(int $id): void
    {
        $success = $this->categoryRepository->restore($id);

        if ($success) {
            $_SESSION['success'] = "Category restored successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to restore category"];
        }

        header('Location: /admin/categories');
        exit;
    }
}