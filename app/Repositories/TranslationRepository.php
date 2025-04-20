<?php

namespace App\Repositories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TranslationRepository
{
    protected $model;
    protected $cachePrefix = 'translation_';
    protected $cacheTtl = 3600; // 1 hour

    public function __construct(Translation $model)
    {
        $this->model = $model;
    }

    public function create(array $data, array $tagIds = []): Translation
    {
        $translation = $this->model->create($data);
        
        if (!empty($tagIds)) {
            $translation->tags()->sync($tagIds);
        }
        
        // Invalidate cache
        $this->invalidateCache($translation->language->code);
        
        return $translation;
    }

    public function update(int $id, array $data, array $tagIds = []): ?Translation
    {
        $translation = $this->model->with('language')->findOrFail($id);
        $oldLocale = $translation->language->code;
        
        $translation->update($data);
        
        if (!empty($tagIds)) {
            $translation->tags()->sync($tagIds);
        }
        
        // Invalidate cache for both old and new locale if changed
        $this->invalidateCache($oldLocale);
        if ($oldLocale !== $translation->language->code) {
            $this->invalidateCache($translation->language->code);
        }
        
        return $translation;
    }

    public function find(int $id): ?Translation
    {
        return $this->model->with(['tags','language'])->find($id);
    }

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['tags','language']);
        
        if (isset($filters['key'])) {
            $query->where('key', 'like', '%' . $filters['key'] . '%');
        }
        
        if (isset($filters['locale'])) {
            $query->whereRelation(
                'language', 'code', '=', $filters['locale']
            );
        }
        
        if (isset($filters['content'])) {
            $query->where('content', 'like', '%' . $filters['content'] . '%');
        }
        
        if (isset($filters['tag_id'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('tags.id', $filters['tag_id']);
            });
        }
        
        if (isset($filters['tag_name'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('tags.name', 'like', '%' . $filters['tag_name'] . '%');
            });
        }
        
        return $query->latest()->paginate($perPage);
    }

    public function getAllByLocale(string $locale): Collection
    {
        $cacheKey = $this->cachePrefix . $locale;
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($locale) {
            return $this->model->whereRelation(
                'language', 'code', '=', $locale
            )->get();
        });
    }

    public function getFormattedTranslations(string $locale): array
    {
        $cacheKey = $this->cachePrefix . 'formatted_' . $locale;
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($locale) {
            $translations = $this->model->whereRelation(
                'language', 'code', '=', $locale
            )->get();
            return $this->formatTranslationsAsNestedArray($translations);
        });
    }

    private function formatTranslationsAsNestedArray(Collection $translations): array
    {
        $result = [];
        
        foreach ($translations as $translation) {
            $this->setNestedArrayValue($result, $translation->key, $translation->content);
        }
        
        return $result;
    }

    private function setNestedArrayValue(array &$array, string $path, $value): void
    {
        $keys = explode('.', $path);
        
        $current = &$array;
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
        
        $current = $value;
    }

    public function invalidateCache(string $locale): void
    {
        Cache::forget($this->cachePrefix . $locale);
        Cache::forget($this->cachePrefix . 'formatted_' . $locale);
    }
}
