<?php

namespace App\Services;

use App\Models\Tag;
use App\Repositories\TagRepository;

class TagService
{
    private TagRepository $tagRepo;

    public function __construct()
    {
        $this->tagRepo = new TagRepository();
    }

    public function createTag(string $name): ?Tag
    {
        
        if ($this->tagRepo->findByName($name)) {
            return null;
        }

        $success = $this->tagRepo->create($name);
        if (!$success) {
            return null;
        }

        return $this->tagRepo->findByName($name);
    }

    public function updateTag(string $oldName, string $newName): bool
    {
        
        if (!$this->tagRepo->findByName($oldName)) {
            return false;
        }

        
        if ($oldName !== $newName && $this->tagRepo->findByName($newName)) {
            return false;
        }

        return $this->tagRepo->update($oldName, $newName);
    }

    public function deleteTag(string $name): bool
    {
        return $this->tagRepo->delete($name);
    }

    public function getAllTags(): array
    {
        return $this->tagRepo->findAll();
    }

    public function getTag(string $name): ?Tag
    {
        return $this->tagRepo->findByName($name);
    }

    public function getPopularTags(int $limit = 10): array
    {
        return $this->tagRepo->findPopular($limit);
    }

    public function tagExists(string $name): bool
    {
        return $this->tagRepo->findByName($name) !== null;
    }

    public function getTagsByJob(int $jobOfferId): array
    {
        return $this->tagRepo->findByJobOfferId($jobOfferId);
    }
}