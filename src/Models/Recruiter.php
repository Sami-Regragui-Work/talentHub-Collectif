<?php

namespace App\Models;

use App\enumTypes\RoleName;

class Recruiter
{
    private User $user;
    private string $companyName;

    public function __construct(User $user, array $data)
    {
        $this->user = $user;
        $this->companyName = (string) $data['company_name'];
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): void
    {
        $this->companyName = $companyName;
    }

    
    public function canPublishJobOffers(): bool
    {
        return $this->user->getRole()->getStringName() == RoleName::RECRUITER;
    }
}