<?php

namespace App\Models;

use DateTimeImmutable;

class User
{
    private int $id;
    private string $full_name;
    private string $email;
    private string $password;
    private readonly DateTimeImmutable $created_at;
    private ?DateTimeImmutable $updated_at;

    private readonly Role $role;

    public function __construct(array $data, Role $role)
    {
        $this->id = (int) $data["id"];
        $this->full_name = (string) $data["fullname"];
        $this->email = (string) $data["email"];
        $this->setPassword((string) $data["password"]);
        $this->created_at = new DateTimeImmutable($data["created_at"]);
        $this->updated_at = isset($data["updated_at"]) ? new DateTimeImmutable($data["updated_at"]) : null;

        $this->role = $role;
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getFullName(): string
    {
        return $this->full_name;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getPassword(): string
    {
        return $this->password;
    }
    public function getRole(): Role
    {
        return $this->role;
    }
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function setFullName(string $full_name): void
    {
        $this->full_name = $full_name;
    }
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
    public function setPassword(string $password): void
    {
        $this->password = str_starts_with($password, "$2y") ? $password : password_hash($password, PASSWORD_DEFAULT);
    }
    public function setUpdatedAt(?DateTimeImmutable $updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}
