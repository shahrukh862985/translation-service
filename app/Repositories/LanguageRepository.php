<?php

namespace App\Repositories;

use App\Models\Language;

class LanguageRepository
{
    protected $model;

    public function __construct(Language $model)
    {
        $this->model = $model;
    }


    public function findOrFail(string $code): ?Language
    {
        $lang = $this->model->where('code', $code)->first();
        if (!$lang) {
            throw new \Illuminate\Support\ItemNotFoundException("Language not found: " . $code);
        }
        return $lang;
    }
}
