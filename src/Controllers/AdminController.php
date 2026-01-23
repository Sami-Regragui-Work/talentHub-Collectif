<?php

namespace App\Controllers;

use App\Interfaces\DashboardInterface;
use App\Repositories\UserRepository;
use App\Repositories\JobRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\TagRepository;
use App\Repositories\CompanyRepository;

class AdminController extends BaseController implements DashboardInterface
{
    private UserRepository $userRepo;
    private JobRepository $jobRepo;
    private CategoryRepository $catRepo;
    private TagRepository $tagRepo;
    private CompanyRepository $companyRepo;

    public function __construct()
    {
        parent::__construct();
        $this->userRepo = new UserRepository();
        $this->jobRepo = new JobRepository();
        $this->catRepo = new CategoryRepository();
        $this->tagRepo = new TagRepository();
        $this->companyRepo = new CompanyRepository();
    }

    public function dashboard(): void
    {
        $this->requireRole('admin');
        
        $stats = [
            'categories' => count($this->catRepo->findAll()),
            'tags' => count($this->tagRepo->findAll()),
            'recruiters' => count($this->userRepo->findByRoleName('recruiter')),
            'offers' => count($this->jobRepo->findAllActive()),
            'candidates' => count($this->userRepo->findByRoleName('candidate'))
        ];
        
        $this->render('admin/dashboard.twig', ['stats' => $stats]);
    }

    public function showRecruiters(): void
    {
        $this->requireRole('admin');
        
        $recruiters = $this->companyRepo->findAll();
        $this->render('admin/recruiters/index.twig', ['recruiters' => $recruiters]);
    }

    public function showCandidates(): void
    {
        $this->requireRole('admin');
        
        $candidates = $this->userRepo->findByRoleName('candidate');
        $this->render('admin/candidates/index.twig', ['candidates' => $candidates]);
    }

    public function showCategories(): void
    {
        $this->requireRole('admin');
        
        $categories = $this->catRepo->findAll();
        $this->render('admin/categories/index.twig', ['categories' => $categories]);
    }

    public function showTags(): void
    {
        $this->requireRole('admin');
        
        $tags = $this->tagRepo->findAll();
        $this->render('admin/tags/index.twig', ['tags' => $tags]);
    }

    public function showOffers(): void
    {
        $this->requireRole('admin');
        
        $offers = $this->jobRepo->findAll(); // Including archived
        $this->render('admin/offers/index.twig', ['offers' => $offers]);
    }
}