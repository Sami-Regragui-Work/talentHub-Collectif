<?php

namespace App\Repositories;

use App\Models\Tag;
use PDO;

class TagRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'tags';
    }

    protected function toObject(array $data): Tag
    {
        return new Tag($data);
    } 

    public function findByName(string $name): ?Tag
    {
        return $this->findOneBy(['name' => $name], [PDO::PARAM_STR]);
    }

    public function exists(string $name): bool
    {
        return $this->findByName($name) !== null;
    }
}
