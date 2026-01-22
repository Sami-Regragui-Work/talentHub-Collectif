<?php

namespace App\Repositories;

use App\Models\CV;

class CVRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'cvs';
    }

    protected function toObject(array $data): CV
    {
        return new CV($data);
    }
}
