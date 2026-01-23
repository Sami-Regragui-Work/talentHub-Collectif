<?php

namespace App\Repositories;

use App\Models\Job;
use Exception;
use PDO;
use PDOException;

class JobRepository extends BaseRepository
{
    private CategoryRepository $category_repo;
    private RecruiterRepository $recruiter_repo;
    private TagRepository $tag_repo;

    public function __construct()
    {
        parent::__construct();
        $this->category_repo = new CategoryRepository();
        $this->recruiter_repo = new RecruiterRepository();
        $this->tag_repo = new TagRepository();
    }

    protected function getTableName(): string
    {
        return 'job_offers';
    }

    protected function toObject(array $data): Job
    {
        $category = $this->category_repo->findByName($data['category_name']);
        if (!$category) {
            throw new Exception('Category not found for job');
        }

        $recruiter = $this->recruiter_repo->findById($data['recruiter_id']);
        if (!$recruiter) {
            throw new Exception('Recruiter not found for job');
        }

        $tags = $this->findTagsForJob($data['id']);

        return new Job($data, $category, $recruiter, $tags);
    }

    public function findByRecruiterId(int $recruiterId): array
    {
        return $this->findBy(['recruiter_id' => $recruiterId], [PDO::PARAM_INT]);
    }

    public function findByCategoryName(string $categoryName): array
    {
        return $this->findBy(['category_name' => $categoryName], [PDO::PARAM_STR]);
    }

    public function findActive(): array
    {
        return $this->findBy(['is_archived' => false], [PDO::PARAM_BOOL]);
    }

    public function findArchived(): array
    {
        return $this->findBy(['is_archived' => true], [PDO::PARAM_BOOL]);
    }

    private function findTagsForJob(int $jobId): array
    {
        try {
            $sql = <<<SQL
            SELECT t.*
            FROM tags t
            INNER JOIN job_offer_tags jot ON t.name = jot.tag_name
            WHERE jot.job_offer_id = ?
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $jobId, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();

            return array_map(fn($row) => $this->tag_repo->toObject($row), $results);
        } catch (PDOException $e) {
            error_log($this::class . ' findTagsForJob error: ' . $e->getMessage());
            return [];
        }
    }

    public function attachTags(int $jobId, array $tagNames): bool
    {
        try {
            foreach ($tagNames as $tagName) {
                $sql = <<<SQL
                INSERT INTO job_offer_tags
                (job_offer_id, tag_name)
                VALUES (?, ?)
                SQL;

                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(1, $jobId, PDO::PARAM_INT);
                $stmt->bindValue(2, $tagName, PDO::PARAM_STR);
                $stmt->execute();
            }
            return true;
        } catch (PDOException $e) {
            error_log($this::class . ' attachTags error: ' . $e->getMessage());
            return false;
        }
    }

    public function detachTag(int $jobId, string $tagName): bool
    {
        try {
            $sql = <<<SQL
            DELETE
            FROM job_offer_tags
            WHERE job_offer_id = ? AND tag_name = ?
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $jobId, PDO::PARAM_INT);
            $stmt->bindValue(2, $tagName, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($this::class . ' detachTag error: ' . $e->getMessage());
            return false;
        }
    }

    public function detachAllTags(int $jobId): bool
    {
        try {
            $sql = <<<SQL
            DELETE
            FROM job_offer_tags
            WHERE job_offer_id = ?
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $jobId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($this::class . ' detachAllTags error: ' . $e->getMessage());
            return false;
        }
    }

    public function syncTags(int $jobId, array $tagNames): bool
    {
        try {
            $this->pdo->beginTransaction();

            $this->detachAllTags($jobId);
            if (!empty($tagNames)) {
                $this->attachTags($jobId, $tagNames);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log($this::class . ' syncTags error: ' . $e->getMessage());
            return false;
        }
    }

    public function search(
        ?string $keyword = null,
        ?string $categoryName = null,
        ?array $tagNames = null,
        bool $includeArchived = false
    ): array {
        try {
            $conditions = [];
            $params = [];
            $types = [];

            if (!$includeArchived) {
                $conditions[] = 'j.is_archived = ?';
                $params[] = false;
                $types[] = PDO::PARAM_BOOL;
            }

            if ($keyword) {
                $conditions[] = '(j.title LIKE ? OR j.description LIKE ?)';
                $params[] = "%{$keyword}%";
                $params[] = "%{$keyword}%";
                $types[] = PDO::PARAM_STR;
                $types[] = PDO::PARAM_STR;
            }

            if ($categoryName) {
                $conditions[] = 'j.category_name = ?';
                $params[] = $categoryName;
                $types[] = PDO::PARAM_STR;
            }

            $sql = <<<SQL
            SELECT DISTINCT j.*
            FROM job_offers j
            SQL;

            if ($tagNames && !empty($tagNames)) {
                $sql .= <<<SQL
                
                INNER JOIN job_offer_tags jot ON j.id = jot.job_offer_id
                SQL;

                $placeholders = implode(',', array_fill(0, count($tagNames), '?'));
                $conditions[] = "jot.tag_name IN ({$placeholders})";
                foreach ($tagNames as $tagName) {
                    $params[] = $tagName;
                    $types[] = PDO::PARAM_STR;
                }
            }

            if (!empty($conditions)) {
                $whereClause = implode(' AND ', $conditions);
                $sql .= <<<SQL
                
                WHERE {$whereClause}
                SQL;
            }

            $sql .= <<<SQL
            
            ORDER BY j.created_at DESC
            SQL;

            $stmt = $this->pdo->prepare($sql);

            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value, $types[$index]);
            }

            $stmt->execute();
            $results = $stmt->fetchAll();

            return array_map(fn($row) => $this->toObject($row), $results);
        } catch (PDOException $e) {
            error_log($this::class . ' search error: ' . $e->getMessage());
            return [];
        }
    }
}
