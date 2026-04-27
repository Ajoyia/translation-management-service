<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'max:10'],
            'content' => ['required', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'Translation key is required',
            'locale.required' => 'Locale is required',
            'content.required' => 'Translation content is required',
        ];
    }
}
