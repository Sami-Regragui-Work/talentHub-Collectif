<?php

namespace App\Repositories;

use App\Core\Database;
use App\enumTypes\RoleName;
use App\Models\Role;
use PDO;
use PDOException;

class RoleRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    private function toObject(array $data): Role
    {
        return new Role($data);
    }

    public function findByName(RoleName $role_name): ?Role
    {
        try {
            $sql = <<<SQL
            SELECT * 
            FROM roles 
            WHERE name = ?
            SQL;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$role_name->value]);

            $data = $stmt->fetch();

            if (!$data) {
                return null;
            }

            return $this->toObject($data);
        } catch (PDOException $e) {
            error_log("RoleRepository findByName error: " . $e->getMessage());
            return null;
        }
    }

    public function findAll(): array
    {
        try {
            $sql = <<<SQL
            SELECT * 
            FROM roles
            SQL;
            $stmt = $this->pdo->query($sql);
            $res = $stmt->fetchAll();

            return array_map(fn($row) => $this->toObject($row), $res);
        } catch (PDOException $e) {
            error_log("RoleRepository findAll error: " . $e->getMessage());
            return [];
        }
    }
}
