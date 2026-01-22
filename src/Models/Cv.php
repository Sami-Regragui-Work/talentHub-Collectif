<?php

class cv
{

    private int $id;
    private string $path;
    private string $fileName;


    public function __construct($id, $path, $fileName)
    {
        $this->id = $id;
        $this->path = $path;
        $this->fileName = $fileName;
    }

    // getters

    public function getId(): int
    {
        return $this->id;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    // setters

    public function setPath($path): void
    {
        $this->path = $path;
    }

    public function setFileName($fileName): void
    {
        $this->fileName = $fileName;
    }
}
