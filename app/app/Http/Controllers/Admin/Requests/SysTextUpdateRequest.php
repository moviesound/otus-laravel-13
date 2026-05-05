<?php

namespace App\Http\Controllers\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SysTextUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'alias' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]*$/'],
            'context' => ['required', 'string', 'not_regex:/^\s*$/'],
        ];
    }

    public function toDTOArray(): array
    {
        $validated = $this->validated();

        return [
            'id' => (int) $this->route('id'),
            'alias' => $validated['alias'],
            'context' => $validated['context'],
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
        ];
    }
}
