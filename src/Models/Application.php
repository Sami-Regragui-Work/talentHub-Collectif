<?php 

class Application {
    private int $id ;
    private int $cvId;
    private string $status;
    private int $userId;
    private int $jobOfferId;
    private string $appliedAt;

    public function __construct($id ,$cvId , $status, $userId, $jobOfferId , $appliedAt){
            $this->id= $id;
            $this->cvId = $cvId;
            $this->status = $status;
            $this->userId = $userId;
            $this-> jobOfferId  = $jobOfferId ;
            $this->appliedAt = $appliedAt;

    }

    public function getId(): int {
        return $this->id;

    }

    public function getCvId(): int {
        return $this->cvId;

    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getUserId(): int {
        return $this->userId;

    }

    public function getJobOfferId(): int {
        return $this->jobOfferId;

    }
    public function getAppliedAt(): string
    {
        return $this->appliedAt;
    }


    public function setCvId(int $cvId): void {
         $this->cvId = $cvId;

    }

    public function setStatus(string $status): void{
         $this->status = $status;
    }

    public function setUserId(int $userId): void {
         $this->userId = $userId;

    }

    public function setJobOfferId(int $jobOfferId): void {
        $this->jobOfferId = $jobOfferId;

    }
    public function setAppliedAt(string $appliedAt): void
    {
         $this->appliedAt = $appliedAt;
    }



}