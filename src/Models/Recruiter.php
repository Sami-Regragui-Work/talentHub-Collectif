<?php

namespace App\Models;

use App\enumTypes\RoleName;

class Recruiter extends User
{
    private string $companyName;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->companyName = (string) $data['company_name'];
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): void
    {
        $this->companyName = $companyName;
    }

    
    public function canPublishJobs(): bool
    {
        return $this->getRole()->getStringName() == RoleName::RECRUITER;
    }
}