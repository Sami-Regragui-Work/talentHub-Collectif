<?php

namespace App\Repositories;

use App\Models\Application;
use PDO;
use PDOException;

class ApplicationRepository extends BaseRepository
{
    private CVRepository $cv_repo;
    private UserRepository $user_repo;
    private JobRepository $job_repo;

    public function __construct()
    {
        parent::__construct();
        $this->cv_repo = new CVRepository();
        $this->user_repo = new UserRepository();
        $this->job_repo = new JobRepository();
    } 

    protected function getTableName(): string
    {
        return 'applications';
    }

    protected function toObject(array $data): Application
    {
        $application = new Application($data);

        if ($data['cv_id']) {
            $cv = $this->cv_repo->findById($data['cv_id']);
            if ($cv) {
                $application->setCv($cv);
            }
        }

        $user = $this->user_repo->findById($data['user_id']);
        if ($user) {
            $application->setUser($user);
        }

        $job = $this->job_repo->findById($data['job_offer_id']);
        if ($job) {
            $application->setJob($job);
        }

        return $application;
    }

    public function findByUserId(int $userId): array
    {
        return $this->findBy(['user_id' => $userId], [PDO::PARAM_INT]);
    }

    public function findByJobOfferId(int $jobOfferId): array
    {
        return $this->findBy(['job_offer_id' => $jobOfferId], [PDO::PARAM_INT]);
    }

    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status], [PDO::PARAM_STR]);
    }

    public function findPending(): array
    {
        return $this->findByStatus('pending');
    }

    public function findAccepted(): array
    {
        return $this->findByStatus('accepted');
    }

    public function findRejected(): array
    {
        return $this->findByStatus('rejected');
    }

    public function updateStatus(int $id, string $status): ?Application
    {
        if (!in_array($status, ['pending', 'accepted', 'rejected'])) {
            return null;
        }

        return $this->update($id, ['status' => $status], [PDO::PARAM_STR]);
    }

    public function accept(int $id): ?Application
    {
        return $this->updateStatus($id, 'accepted');
    }

    public function reject(int $id): ?Application
    {
        return $this->updateStatus($id, 'rejected');
    }

    public function hasUserApplied(int $userId, int $jobOfferId): bool
    {
        try {
            $sql = <<<SQL
            SELECT COUNT(*)
            FROM applications
            WHERE user_id = ? AND job_offer_id = ?
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $jobOfferId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log($this::class . ' hasUserApplied error: ' . $e->getMessage());
            return false;
        }
    }

    public function countByJobOfferId(int $jobOfferId): int
    {
        try {
            $sql = <<<SQL
            SELECT COUNT(*)
            FROM applications
            WHERE job_offer_id = ?
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $jobOfferId, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log($this::class . ' countByJobOfferId error: ' . $e->getMessage());
            return 0;
        }
    }

    public function findByRecruiter(int $recruiterId): array
    {
        try {
            $sql = <<<SQL
            SELECT a.*
            FROM applications a
            INNER JOIN job_offers j ON a.job_offer_id = j.id
            WHERE j.recruiter_id = ?
            ORDER BY a.applied_at DESC
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $recruiterId, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();

            return array_map(fn($row) => $this->toObject($row), $results);
        } catch (PDOException $e) {
            error_log($this::class . ' findByRecruiter error: ' . $e->getMessage());
            return [];
        }
    }
}
