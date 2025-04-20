<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationUpdateRequest extends FormRequest
{
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
        $translationId = $this->route('translation');

        $languageId = null;
        if ($this->has('locale')) {
            $language = app()->make('App\Repositories\LanguageRepository')->findOrFail($this->input('locale'));
            $languageId = $language ? $language->id : null;
        }
        return [
            'key' => [
                'sometimes',
                'required',
                'string',
                'max:191',
                Rule::when($this->has('key') && $languageId, function () use ($translationId, $languageId) {
                    return Rule::unique('translations')->where(function ($query) use ($languageId) {
                        return $query->where('language_id', $languageId);
                    })->ignore($translationId);
                }),
            ],
            'locale' => 'sometimes|required|string|max:10',
            'content' => 'sometimes|required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];
    }
}
