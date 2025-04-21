<?php

namespace App\Repositories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TranslationRepository
{
    protected $model;

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

        return $translation;
    }

    public function find(int $id): ?Translation
    {
        return $this->model->with(['tags', 'language'])->find($id);
    }

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['tags', 'language']);

        if (isset($filters['key'])) {
            $query->where('key', 'like', '%' . $filters['key'] . '%');
        }

        if (isset($filters['locale'])) {
            $query->whereRelation(
                'language',
                'code',
                '=',
                $filters['locale']
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
}
