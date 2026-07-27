<?php

namespace App\Http\Controllers\Bot\TelegramWebhook\Requests;

use App\DTO\Bot\TelegramWebhook\TelegramMessageDTO;
use App\Enums\Bot\TelegramMessageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TelegramWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->query('key') === config('services.telegram.key');
    }

    public function rules(): array
    {
        return [
            'update_id' => ['required', 'integer'],

            'message' => ['sometimes', 'array'],
            'callback_query' => ['sometimes', 'array'],
        ];
    }

    private function resolveType(): ?TelegramMessageType
    {
        return match (true) {
            $this->has('callback_query') => TelegramMessageType::CallbackQuery,
            $this->has('message') => TelegramMessageType::Message,
            default => null,
        };
    }

    /**
     * Возвращает тип (строго enum)
     */
    public function telegramType(): TelegramMessageType
    {
        return $this->resolveType()
            ?? throw ValidationException::withMessages([
                'error' => 'Unsupported telegram message type',
            ]);
    }

    public function chatId(): int
    {
        return match ($this->telegramType()) {
            TelegramMessageType::CallbackQuery =>
            (int) $this->input('callback_query.message.chat.id'),

            TelegramMessageType::Message =>
            (int) $this->input('message.chat.id'),
        };
    }

    public function toDTO(): TelegramMessageDTO
    {
        $type = $this->telegramType();

        return new TelegramMessageDTO(
            chatId: $this->chatId(),
            type: $type,
            payload: $this->validated(),
        );
    }
}
