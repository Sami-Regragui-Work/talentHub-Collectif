<?php

namespace App\Models;

class CV
{
    private int $id;
    private ?string $path;
    private ?string $filename;

    public function __construct(array $data)
    {
        $this->id = (int) $data['id'];
        $this->path = $data['path'] ?? null;
        $this->filename = $data['filename'] ?? null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }


    public function getAbsolutePath(): string
    {
        if ($this->path === null) {
            return '';
        }
        return __DIR__ . '/../../public' . $this->path;
    }
}