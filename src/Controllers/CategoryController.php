<?php

namespace App\Controllers;

use App\Repositories\CategoryRepository;

class CategoryController extends BaseController
{
    private CategoryRepository $repo;

    public function __construct()
    {
        parent::__construct();
        $this->repo = new CategoryRepository();
    }

    public function index(): void
    {
        $this->requireRole('admin');
        $this->render('admin/categories/index.twig', [
            'categories' => $this->repo->findAll()
        ]);
    }

    public function create(): void
    {
        $this->requireRole('admin');
        $this->render('admin/categories/create.twig');
    }

    public function store(): void
    {
        $this->requireRole('admin');
        
        if (empty($_POST['name'])) {
            $this->setFlash('error', "Le nom est requis");
            $this->redirect('/admin/categories/create');
        }
        
        try {
            $this->repo->create($_POST['name']);
            $this->setFlash('success', "Catégorie créée");
            $this->redirect('/admin/categories');
        } catch (\Exception $e) {
            $this->setFlash('error', "Cette catégorie existe déjà");
            $this->redirect('/admin/categories/create');
        }
    }

    public function edit(string $name): void
    {
        $this->requireRole('admin');
        
        $category = $this->repo->findByName($name);
        if (!$category) {
            $this->renderError(404);
        }
        
        $this->render('admin/categories/edit.twig', ['category' => $category]);
    }

    public function update(string $name): void
    {
        $this->requireRole('admin');
        
        if (empty($_POST['new_name'])) {
            $this->setFlash('error', "Le nouveau nom est requis");
            $this->redirect("/admin/categories/edit/$name");
        }
        
        $this->repo->update($name, $_POST['new_name']);
        $this->setFlash('success', "Catégorie mise à jour");
        $this->redirect('/admin/categories');
    }

    public function delete(string $name): void
    {
        $this->requireRole('admin');
        $this->repo->delete($name);
        $this->setFlash('success', "Catégorie supprimée");
        $this->redirect('/admin/categories');
    }
}