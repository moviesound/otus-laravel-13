<?php

namespace App\Http\Controllers\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\AllowedLang;

class SysTextSearchRequest extends FormRequest
{
    public function rules(): array
    {
        $langs = config('langs.list');
        return [
            'alias' => ['nullable', 'string', 'regex:/^[a-z][a-z0-9_]*$/'],
            'lang' => ['nullable', 'string', new AllowedLang($langs)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDTOArray(): array
    {
        $validated = $this->validated();
        $langs = config('langs.list');
        return [
            'alias' => $validated['alias'] ?? null,
            'lang' => $validated['lang'] ?? $langs[0] ?? 'ru',
            'perPage' => $validated['per_page'] ?? 20,
        ];
    }

    public function messages(): array
    {
        return [
            'lang.required' => 'Не указан язык',

            'alias.regex' => 'alias должен начинаться с латинской буквы,
            содержать только латинские буквы в нижнем регистре,
            цифры и нижнее подчеркивание',

            'per_page.min' => 'perPage не может быть меньше 1',
            'per_page.max' => 'perPage не может быть больше 100',
            'per_page.integer' => 'perPage должен быть числом',
        ];
    }
}
