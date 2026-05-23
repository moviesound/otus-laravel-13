<?php

namespace App\Http\Controllers\Admin\Requests;

use App\DTO\ActionLogSearchDTO;
use Illuminate\Foundation\Http\FormRequest;

class ActionLogSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDTO(): ActionLogSearchDTO
    {
        $validated = $this->validated();

        return new ActionLogSearchDTO(
            search: $validated['search'] ?? null,
            userId: $validated['userId'] ?? null,
            perPage: $validated['perPage'] ?? 30,
        );
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
