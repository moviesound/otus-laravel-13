<?php

namespace App\Http\Controllers\Admin\Requests;

use App\DTO\AdminUpdateDTO;
use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'roles' => ['required', 'array'],
            'roles.*' => ['string'],
        ];
    }

    public function toDTO(): AdminUpdateDTO
    {
        $validated = $this->validated();

        return new AdminUpdateDTO(
            id: (int) $this->route('id'),
            name: $validated['name'],
            email: $validated['email'],
            roles: $validated['roles'],
        );
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно',
            'email.required' => 'Почта обязательна',
            'email.email' => 'Некорректная почта',
            'roles.required' => 'Выберите хотя бы одну роль',
        ];
    }
}
