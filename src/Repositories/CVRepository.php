<?php

namespace App\Repositories;

use App\Models\CV;

class CVRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'cvs';
    }

    public function toObject(array $data): CV
    {
        return new CV($data);
    }
}
