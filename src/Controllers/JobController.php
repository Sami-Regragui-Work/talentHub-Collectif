<?php

namespace App\Controllers;

use App\Repositories\JobRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\TagRepository;

class JobController extends BaseController
{
    private JobRepository $jobRepo;
    private CategoryRepository $catRepo;
    private TagRepository $tagRepo;

    public function __construct()
    {
        parent::__construct();
        $this->jobRepo = new JobRepository();
        $this->catRepo = new CategoryRepository();
        $this->tagRepo = new TagRepository();
    }

    // Public routes
    public function index(): void
    {
        $jobs = $this->jobRepo->findAllActive();
        $this->render('guest/job.twig', ['jobs' => $jobs]);
    }

    public function show(int $id): void
    {
        $job = $this->jobRepo->findById($id);
        
        if (!$job) {
            $this->renderError(404);
        }
        
        $this->render('guest/job_detail.twig', ['job' => $job]);
    }

    // Recruiter routes
    public function recruiterIndex(): void
    {
        $this->requireRole('recruiter');
        
        $jobs = $this->jobRepo->findByRecruiter($this->currentUser->getId());
        $this->render('recruiter/offers/index.twig', ['offers' => $jobs]);
    }

    public function create(): void
    {
        $this->requireRole('recruiter');
        
        $this->render('recruiter/offers/create.twig', [
            'categories' => $this->catRepo->findAll(),
            'tags' => $this->tagRepo->findAll()
        ]);
    }

    public function store(): void
    {
        $this->requireRole('recruiter');
        
        if (empty($_POST['title']) || empty($_POST['description'])) {
            $this->setFlash('error', "Titre et description requis");
            $this->redirect('/recruiter/offers/create');
        }

        $this->jobRepo->create([
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'salary' => $_POST['salary'] ?? null,
            'category' => $_POST['category'],
            'recruiter_id' => $this->currentUser->getId()
        ]);

        $this->setFlash('success', "Offre publiée avec succès");
        $this->redirect('/recruiter/offers');
    }

    public function edit(int $id): void
    {
        $job = $this->jobRepo->findById($id);
        
        if (!$job) {
            $this->renderError(404);
        }
        
        $this->requireOwnerOrAdmin($job->getRecruiterId());
        
        $this->render('recruiter/offers/edit.twig', [
            'job' => $job,
            'categories' => $this->catRepo->findAll(),
            'tags' => $this->tagRepo->findAll()
        ]);
    }

    public function update(int $id): void
    {
        $job = $this->jobRepo->findById($id);
        
        if (!$job) {
            $this->renderError(404);
        }
        
        $this->requireOwnerOrAdmin($job->getRecruiterId());
        
        $this->jobRepo->update($id, [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'salary' => $_POST['salary'],
            'category' => $_POST['category']
        ]);
        
        $this->setFlash('success', "Offre mise à jour");
        $this->redirect('/recruiter/offers');
    }

    public function archive(int $id): void
    {
        $job = $this->jobRepo->findById($id);
        
        if (!$job) {
            $this->renderError(404);
        }
        
        $this->requireOwnerOrAdmin($job->getRecruiterId());
        
        $this->jobRepo->archive($id);
        
        $referer = $_SERVER['HTTP_REFERER'] ?? '/recruiter/offers';
        $this->redirect($referer);
    }
}