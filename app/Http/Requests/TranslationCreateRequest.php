<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationCreateRequest extends FormRequest
{
    protected $langRepository;
    public function __construct()
    {
        $this->langRepository = app('App\Repositories\LanguageRepository');
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $language = $this->langRepository->findOrFail($this->input('locale'));
        $languageId = $language ? $language->id : null;
        return [
            'key' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('translations')->where(function ($query) use ($languageId) {
                    return $query->where('language_id', $languageId);
                }),
            ],
            'locale' => 'required|string|max:10',
            'content' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'string|max:50',
        ];
    }
}
