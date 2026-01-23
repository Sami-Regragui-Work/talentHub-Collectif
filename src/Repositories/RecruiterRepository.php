<?php

namespace App\Repositories;

use App\enumTypes\RoleName;
use App\Models\Recruiter;
use PDO;
use PDOException;

class RecruiterRepository extends BaseRepository
{
    private RoleRepository $role_repo;

    public function __construct()
    {
        parent::__construct();
        $this->role_repo = new RoleRepository();
    }

    protected function getTableName(): string
    {
        return 'recruiters';
    }

    protected function toObject(array $data): Recruiter
    {
        // Fetch user data joined with recruiter data
        try {
            $sql = <<<SQL
            SELECT u.*, r.company_name
            FROM users u
            INNER JOIN recruiters r ON u.id = r.id
            WHERE r.id = ?
            SQL;

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(1, $data['id'], PDO::PARAM_INT);
            $stmt->execute();
            $fullData = $stmt->fetch();

            if (!$fullData) {
                throw new \Exception('User data not found for recruiter');
            }

            // Get the role
            $roleName = RoleName::from($fullData['role_name']);
            $role = $this->role_repo->findByName($roleName);

            if (!$role) {
                throw new \Exception('Role not found for recruiter');
            }

            // Prepare complete data array
            $recruiterData = [
                'id' => $fullData['id'],
                'fullname' => $fullData['name'],
                'email' => $fullData['email'],
                'password' => $fullData['password'],
                'created_at' => $fullData['created_at'],
                'role_name' => $fullData['role_name'],
                'company_name' => $fullData['company_name']
            ];

            return new Recruiter($recruiterData, $role);
        } catch (PDOException $e) {
            error_log($this::class . ' toObject error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function exists(int $id): bool
    {
        return $this->findById($id) !== null;
    }
}
