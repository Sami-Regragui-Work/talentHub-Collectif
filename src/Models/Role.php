<?php

namespace App\Models;

use App\enumTypes\RoleName;

class Role
{
    private readonly RoleName $name;
    private ?string $description;

    public function __construct(array $data)
    {
        $this->name = RoleName::from($data['name']);
        $this->description = $data['description'] ?? null;
    }
    public function getName(): RoleName
    {
        return $this->name;
    }
    public function getStringName(): string
    {
        return $this->name->value;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
