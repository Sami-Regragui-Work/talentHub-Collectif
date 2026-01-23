<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;
use PDOException;

abstract class BaseRepository
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    abstract protected function getTableName(): string;

    abstract public function toObject(array $data): object;

    protected function findBy(array $conditions = [], array $types = []): array
    {
        try {
            $table = $this->getTableName();

            if (empty($conditions)) {
                $sql = <<<SQL
                SELECT *
                FROM {$table}
                SQL;
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
            } else if (count($conditions) !== count($types)) {
                error_log($this::class . ' findBy error: conditions and types count mismatch');
                return [];
            } else {
                $fields = array_keys($conditions);
                $values = array_values($conditions);
                $whereClause = implode(' AND ', array_map(fn($field) => "{$field} = ?", $fields));

                $sql = <<<SQL
                SELECT *
                FROM {$table}
                WHERE {$whereClause}
                SQL;
                $stmt = $this->pdo->prepare($sql);

                foreach ($values as $index => $value) {
                    $stmt->bindValue($index + 1, $value, $types[$index]);
                }
                $stmt->execute();
            }

            $results = $stmt->fetchAll();
            return array_map(fn($row) => $this->toObject($row), $results);
        } catch (PDOException $e) {
            error_log($this::class . ' findBy error: ' . $e->getMessage());
            return [];
        }
    }

    protected function findOneBy(array $condition, array $type): ?object
    {
        $results = $this->findBy($condition, $type);
        return $results[0] ?? null;
    }

    public function findById(int $id): ?object
    {
        return $this->findOneBy(['id' => $id], [PDO::PARAM_INT]);
    }

    public function findAll(): array
    {
        return $this->findBy([]);
    }

    public function create(array $data, array $types): ?object
    {
        try {
            $table = $this->getTableName();
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = array_fill(0, count($fields), '?');

            $columnClause = implode(', ', $fields);
            $valueClause = implode(', ', $placeholders);

            $sql = <<<SQL
            INSERT INTO {$table}
            ({$columnClause})
            VALUES ({$valueClause})
            SQL;

            $stmt = $this->pdo->prepare($sql);

            foreach ($values as $index => $value) {
                $stmt->bindValue($index + 1, $value, $types[$index]);
            }

            $stmt->execute();

            $id = (int) $this->pdo->lastInsertId();
            return $this->findById($id);
        } catch (PDOException $e) {
            error_log($this::class . ' create error: ' . $e->getMessage());
            return null;
        }
    }

    public function update(int $id, array $data, array $types): ?object
    {
        try {
            $table = $this->getTableName();
            $setClause = implode(', ', array_map(fn($field) => "{$field} = ?", array_keys($data)));
            $values = array_values($data);

            $sql = <<<SQL
            UPDATE {$table}
            SET {$setClause}
            WHERE id = ?
            SQL;

            $stmt = $this->pdo->prepare($sql);

            foreach ($values as $index => $value) {
                $stmt->bindValue($index + 1, $value, $types[$index]);
            }

            $stmt->bindValue(count($values) + 1, $id, PDO::PARAM_INT);

            $stmt->execute();

            return $this->findById($id);
        } catch (PDOException $e) {
            error_log($this::class . ' update error: ' . $e->getMessage());
            return null;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $table = $this->getTableName();

            $sql = <<<SQL
            DELETE
            FROM {$table}
            WHERE id = ?
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($this::class . ' delete error: ' . $e->getMessage());
            return false;
        }
    }

    public function archive(int $id): ?object
    {
        return $this->update($id, ['is_archived' => true], [PDO::PARAM_BOOL]);
    }

    public function restore(int $id): ?object
    {
        return $this->update($id, ['is_archived' => false], [PDO::PARAM_BOOL]);
    }
}
