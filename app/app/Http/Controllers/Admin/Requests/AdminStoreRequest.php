<?php

namespace App\Http\Controllers\Admin\Requests;

use App\DTO\AdminStoreDTO;
use Illuminate\Foundation\Http\FormRequest;

class AdminStoreRequest extends FormRequest
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

    public function toDTO(): AdminStoreDTO
    {
        $validated = $this->validated();

        return new AdminStoreDTO(
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

