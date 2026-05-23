<?php

namespace App\Http\Controllers\Admin\Requests;

use App\DTO\AdminSearchDTO;
use Illuminate\Foundation\Http\FormRequest;

class AdminSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDTO(): AdminSearchDTO
    {
        $validated = $this->validated();

        return new AdminSearchDTO(
            name: $validated['name'] ?? null,
            email: $validated['email'] ?? null,
            perPage: $validated['per_page'] ?? 20,
        );
    }
}
