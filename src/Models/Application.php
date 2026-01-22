<?php

namespace App\Models;

use DateTimeImmutable;

class Application
{
    private int $id;
    private ?CV $cv;
    private string $status;
    private User $candidate;
    private JobOffer $jobOffer;
    private readonly DateTimeImmutable $appliedAt;

    public function __construct(array $data, User $candidate, JobOffer $jobOffer, ?CV $cv = null)
    {
        $this->id = (int) $data['id'];
        $this->cv = $cv;
        $this->status = (string) $data['status'];
        $this->candidate = $candidate;
        $this->jobOffer = $jobOffer;
        $this->appliedAt = new DateTimeImmutable($data['applied_at']);
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

    public function getJobOffer(): JobOffer
    {
        return $this->jobOffer;
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