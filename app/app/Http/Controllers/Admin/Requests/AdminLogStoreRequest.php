<?php

namespace App\Http\Controllers\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLogStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'action' => ['required', 'string', 'max:255'],
        ];
    }

    public function toDTOArray(): array
    {
        $validated = $this->validated();

        return [
            'userId' => $validated['user_id'],
            'action' => $validated['action'],
            'ip' => $this->ip(),
            'userAgent' => $this->userAgent(),
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Пользователь обязателен',
            'action.required' => 'Действие обязательно',
        ];
    }
}
