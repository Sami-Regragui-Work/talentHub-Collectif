<?php

namespace App\Repositories;

use App\Core\Database;
use App\enumTypes\RoleName;
use App\Models\User;
use Exception;
use PDO;
use PDOException;

class UserRepository
{
    private PDO $pdo;
    private RoleRepository $role_repo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
        $this->role_repo = new RoleRepository();
    }

    private function toObject(array $res): User
    {
        $roleName = RoleName::from($res["role_name"]);
        $role = $this->role_repo->findByName($roleName);

        if (!$role) throw new Exception("Role not found for user");

        return new User($res, $role);
    }

    private function findBy(array $conditions): array
    {

        try {
            if (empty($conditions)) {
                $sql = <<<SQL
                SELECT *
                FROM users
                SQL;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
            } else {
                $fields = array_keys($conditions);
                $values = array_values($conditions);
                $whereClause = implode(" AND ", array_map(fn($field) => "{$field} = ?", $fields));

                $sql = <<<SQL
                SELECT * 
                FROM users 
                WHERE {$whereClause}
                SQL;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($values);
            }

            $res = $stmt->fetchAll();
            return array_map(fn($row) => $this->toObject($row), $res);
        } catch (PDOException $e) {
            error_log("UserRepository findBy error: " . $e->getMessage());
            return [];
        }
    }

    private function findOneBy(array $condition): ?User
    {
        $res = $this->findBy($condition);
        return $res[0] ?? null;
    }

    public function findById(int $id): ?User
    {
        return $this->findOneBy(["id" => $id]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(["email" => $email]);
    }

    public function findByRoleName(string $role_name): array
    {
        return $this->findBy(["role_name" => $role_name]);
    }

    public function findAll(): array
    {
        return $this->findBy([]);
    }

    public function create(string $fullname, string $email, string $password, RoleName $roleName): User
    {
        try {
            $role = $this->role_repo->findByName($roleName);

            if (!$role) {
                throw new Exception("Role not found");
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = <<<SQL
            INSERT INTO users (fullname, email, password, role_name) 
            VALUES (?, ?, ?, ?)
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$fullname, $email, $hashedPassword, $roleName->value]);

            $userId = (int) $this->pdo->lastInsertId();

            return $this->findById($userId);
        } catch (PDOException $e) {
            error_log("UserRepository create error: " . $e->getMessage());
            throw new Exception("Failed to create user");
        }
    }

    public function emailExists(string $email): bool
    {
        try {
            $sql = <<<SQL
            SELECT COUNT(*)
            FROM users
            WHERE email = ?
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email]);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("UserRepository emailExists error: " . $e->getMessage());
            return false;
        }
    }
}
