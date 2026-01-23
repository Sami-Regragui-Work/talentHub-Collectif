<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class CategoryService
{
    private CategoryRepository $categoryRepo;

    public function __construct()
    {
        $this->categoryRepo = new CategoryRepository();
    }

    public function createCategory(string $name): ?Category
    {
        
        if ($this->categoryRepo->findByName($name)) {
            return null;
        }

        $success = $this->categoryRepo->create($name);
        if (!$success) {
            return null;
        }

        return $this->categoryRepo->findByName($name);
    }

    public function updateCategory(string $oldName, string $newName): bool
    {
        
        if (!$this->categoryRepo->findByName($oldName)) {
            return false;
        }


        if ($oldName !== $newName && $this->categoryRepo->findByName($newName)) {
            return false;
        }

        return $this->categoryRepo->update($oldName, $newName);
    }

    public function deleteCategory(string $name): bool
    {
        
        if ($this->categoryRepo->hasJobs($name)) {
            return false;
        }

        return $this->categoryRepo->delete($name);
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepo->findAll();
    }

    public function getCategory(string $name): ?Category
    {
        return $this->categoryRepo->findByName($name);
    }

    public function getCategoriesWithJobCount(): array
    {
        return $this->categoryRepo->findAllWithJobCount();
    }

    public function categoryExists(string $name): bool
    {
        return $this->categoryRepo->findByName($name) !== null;
    }
}