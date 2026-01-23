<?php

namespace App\Models;

use DateTimeImmutable;

class Application
{
    private int $id;
    private ?CV $cv;
    private string $status;
    private readonly DateTimeImmutable $appliedAt;
    private User $candidate;
    private Job $job;

    public function __construct(array $data, User $candidate, Job $job, ?CV $cv = null)
    {
        $this->id = (int) $data['id'];
        $this->status = (string) $data['status'];
        $this->appliedAt = new DateTimeImmutable($data['applied_at']);
        $this->candidate = $candidate;
        $this->job = $job;
        $this->cv = $cv;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCv(): ?CV
    {
        return $this->cv;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCandidate(): User
    {
        return $this->candidate;
    }

    public function getJob(): Job
    {
        return $this->job;
    }

    public function getAppliedAt(): DateTimeImmutable
    {
        return $this->appliedAt;
    }

    public function setCv(?CV $cv): void
    {
        $this->cv = $cv;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function accept(): void
    {
        $this->status = 'accepted';
    }

    public function reject(): void
    {
        $this->status = 'rejected';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
