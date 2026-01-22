<?php

namespace App\Models;

class Category
{
    private readonly string $name; 

    public function __construct(array $data)
    {
        $this->name = (string) $data['name'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    
}