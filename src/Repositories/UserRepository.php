<?php

namespace App\Repositories;

use App\enumTypes\RoleName;
use App\Models\User;
use Exception;
use PDO;
use PDOException;

class UserRepository extends BaseRepository
{
    private RoleRepository $role_repo;

    public function __construct()
    {
        parent::__construct();
        $this->role_repo = new RoleRepository();
    }

    protected function getTableName(): string
    {
        return 'users';
    }

    protected function toObject(array $data): User
    {
        $roleName = RoleName::from($data['role_name']);
        $role = $this->role_repo->findByName($roleName);

        if (!$role) throw new Exception('Role not found for user');

        return new User($data, $role);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email], [PDO::PARAM_STR]);
    }

    public function findByRoleName(string $role_name): array
    {
        return $this->findBy(['role_name' => $role_name], [PDO::PARAM_STR]);
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
            $stmt->bindValue(1, $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log($this::class . ' emailExists error: ' . $e->getMessage());
            return false;
        }
    }
}
