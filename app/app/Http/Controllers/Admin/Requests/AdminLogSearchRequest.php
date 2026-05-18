<?php

namespace App\Http\Controllers\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLogSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDTOArray(): array
    {
        $validated = $this->validated();

        return [
            'search' => $validated['search'] ?? null,
            'userId' => $validated['user_id'] ?? null,
            'perPage' => $validated['per_page'] ?? 20,
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'Поиск должен быть строкой',
            'user_id.integer' => 'ID пользователя должен быть числом',
            'per_page.integer' => 'Количество элементов должно быть числом',
        ];
    }
}
