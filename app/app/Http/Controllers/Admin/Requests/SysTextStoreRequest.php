<?php

namespace App\Http\Controllers\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\AllowedLang;

class SysTextStoreRequest extends FormRequest
{
    public function rules(): array
    {
        $langs = config('langs.list');

        return [
            'alias' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]*$/'],
            'context' => ['required', 'string', 'not_regex:/^\s*$/',],
            'lang' => ['required', 'string', new AllowedLang($langs)],
        ];
    }

    public function toDTOArray(): array
    {
        $validated = $this->validated();

        return [
            'alias' => $validated['alias'],
            'context' => $validated['context'],
            'lang' => $validated['lang'],
        ];
    }

    public function messages(): array
    {
        return [
            'alias.required' => 'alias обязателен',

            'alias.regex' => 'alias должен начинаться с латинской буквы,
            содержать только латинские буквы в нижнем регистре,
            ифры и нижнее подчеркивание',

            'context.not_regex' => 'Контекст не может состоять только из пробелов',

            'context.required' => 'Вы не заполнили текст',

            'lang.required' => 'Не указан язык',
        ];
    }
}
