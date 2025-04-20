<?php

namespace App\Repositories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TagRepository
{
    protected $model;
    protected $cacheKey = 'tags_all';
    protected $cacheTtl = 3600; // 1 hour

    public function __construct(Tag $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return Cache::remember($this->cacheKey, $this->cacheTtl, function () {
            return $this->model->orderBy('name')->get();
        });
    }

    public function findByName(string $name): ?Tag
    {
        return $this->model->where('name', $name)->first();
    }

    public function create(array $data): Tag
    {
        $tag = $this->model->create($data);
        Cache::forget($this->cacheKey);
        return $tag;
    }

    public function findOrCreate(string $name): Tag
    {
        $tag = $this->findByName($name);
        
        if (!$tag) {
            $tag = $this->create(['name' => $name]);
        }
        
        return $tag;
    }
    public function findOrFail(string $name): ?Tag
    {
        $tag = $this->model->where('name', $name)->first();
        if (!$tag) {
            throw new \Illuminate\Support\ItemNotFoundException("Tag not found: " . $name);
        }
        return $tag;

    }
}
