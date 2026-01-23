<?php

namespace App\Controllers;

use App\Repositories\TagRepository;

class TagController extends BaseController
{
    private TagRepository $repo;

    public function __construct()
    {
        parent::__construct();
        $this->repo = new TagRepository();
    }

    public function index(): void
    {
        $this->requireRole('admin');
        $this->render('admin/tags/index.twig', [
            'tags' => $this->repo->findAll()
        ]);
    }

    public function create(): void
    {
        $this->requireRole('admin');
        $this->render('admin/tags/create.twig');
    }

    public function store(): void
    {
        $this->requireRole('admin');
        
        if (empty($_POST['name'])) {
            $this->setFlash('error', "Le nom est requis");
            $this->redirect('/admin/tags/create');
        }
        
        try {
            $this->repo->create($_POST['name']);
            $this->setFlash('success', "Tag créé");
            $this->redirect('/admin/tags');
        } catch (\Exception $e) {
            $this->setFlash('error', "Ce tag existe déjà");
            $this->redirect('/admin/tags/create');
        }
    }

    public function edit(string $name): void
    {
        $this->requireRole('admin');
        
        $tag = $this->repo->findByName($name);
        if (!$tag) {
            $this->renderError(404);
        }
        
        $this->render('admin/tags/edit.twig', ['tag' => $tag]);
    }

    public function update(string $name): void
    {
        $this->requireRole('admin');
        
        if (empty($_POST['new_name'])) {
            $this->setFlash('error', "Le nouveau nom est requis");
            $this->redirect("/admin/tags/edit/$name");
        }
        
        $this->repo->update($name, $_POST['new_name']);
        $this->setFlash('success', "Tag mis à jour");
        $this->redirect('/admin/tags');
    }

    public function delete(string $name): void
    {
        $this->requireRole('admin');
        $this->repo->delete($name);
        $this->setFlash('success', "Tag supprimé");
        $this->redirect('/admin/tags');
    }
}