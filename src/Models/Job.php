<?php

class jobOffer {

   private int  $id;
   private string  $title; 
   private string $description;
   private float $salary;
   private bool $isArchived;
   private string $createdAt;
   private string $categoryName;
   private int $recruiterId;


   public function __construct($id,$title,$description, $salary,$isArchived,$createdAt,$categoryName,$recruiterId){

                $this->id = $id;
                $this->title = $title;
                $this->description = $description;
                $this->salary = $salary;
                $this->isArchived = $isArchived;
                $this->createdAt = $createdAt;
                $this->categoryName = $categoryName;
                $this->recruiterId = $recruiterId;

   }

   // Getters

   public function getId(): int {
       return  $this->id ;
   }

    public function getTitle(): string {
       return  $this->title ;
    }

    public function getDescription(): string {
       return  $this->description ;
    }

    public function getSalary(): float {
       return  $this->salary;
    }

    public function getIsArchived (): bool {
       return  $this->isArchived  ;
    }

    public function getCreatedAt (): string {
       return  $this->createdAt  ;
    }

    public function getCategoryName (): string {
       return  $this->categoryName  ;
    }
    
    public function getRecruiterId (): int {
       return  $this->recruiterId  ;
    }

    // setters

    public function setTitle($title): void {
         $this->title = $title ;
    }

    public function setDescription($description): void {
         $this->description = $description;
    }

    public function setSalary($salary): void {
         $this->salary = $salary;
    }

    public function setIsArchived ($isArchived): void {
         $this->isArchived = $isArchived ;
    }

    public function setCreatedAt ($createdAt): void{
         $this->createdAt = $createdAt  ;
    }

    public function setCategoryName ($categoryName): void {
         $this->categoryName = $categoryName ;
    }

    public function setRecruiterId ($recruiterId): void {
         $this->recruiterId = $recruiterId ;
    }
  
    

}