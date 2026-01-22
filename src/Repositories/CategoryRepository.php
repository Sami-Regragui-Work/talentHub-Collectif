<?php

namespace App\Repositories;

use App\Models\Category;
use PDO;

class CategoryRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'categories';
    }

    protected function toObject(array $data): Category
    {
        return new Category($data);
    }

    public function findByName(string $name): ?Category
    {
        return $this->findOneBy(['name' => $name], [PDO::PARAM_STR]);
    }

    public function exists(string $name): bool
    {
        return $this->findByName($name) !== null;
    }
}
