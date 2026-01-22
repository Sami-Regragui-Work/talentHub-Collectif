<?php


class recruiter
{
    private int $id;
    private string $companyName;

    public function __construct($id, $companyName)
    {
        $this->id = $id;
        $this->companyName = $companyName;
    }

    //getters 
    public function getId(): int
    {
        return $this->id;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    // setters 

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function setCompanyName($companyName): void
    {
        $this->companyName = $companyName;
    }
}
