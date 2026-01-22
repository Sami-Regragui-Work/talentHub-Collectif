<?php

namespace App\Models;

use DateTimeImmutable;

class Job
{
    private int $id;
    private string $title;
    private string $description;
    private ?float $salary;
    private bool $isArchived;
    private readonly DateTimeImmutable $createdAt;
    private Category $category;
    private Recruiter $recruiter;
    private array $tags = [];

    public function __construct(array $data, Category $category, Recruiter $recruiter, array $tags = [])
    {
        $this->id = (int) $data['id'];
        $this->title = (string) $data['title'];
        $this->description = (string) $data['description'];
        $this->salary = isset($data['salary']) ? (float) $data['salary'] : null;
        $this->isArchived = (bool) ($data['is_archived'] ?? false);
        $this->createdAt = new DateTimeImmutable($data['created_at']);
        $this->category = $category;
        $this->recruiter = $recruiter;
        $this->tags = $tags;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSalary(): ?float
    {
        return $this->salary;
    }

    public function getIsArchived(): bool
    {
        return $this->isArchived;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getRecruiter(): Recruiter
    {
        return $this->recruiter;
    }

    public function getTags(): array
    {
        return $this->tags;
    }


    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setSalary(?float $salary): void
    {
        $this->salary = $salary;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function setRecruiter(Recruiter $recruiter): void
    {
        $this->recruiter = $recruiter;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function addTag(Tag $tag): void
    {
        $this->tags[] = $tag;
    }


    public function archive(): void
    {
        $this->isArchived = true;
    }

    public function isActive(): bool
    {
        return !$this->isArchived;
    }
}
