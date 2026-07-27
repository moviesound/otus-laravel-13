<?php

namespace App\Contracts\Bot\Messengers;

interface MessengerInterface
{
    public function sendMessage(
        string $text,
        array $buttons = [],
        bool $isTemporary = true,
        bool $noSaving = false,
        ?int $queueId = null
    ): mixed;

    public function name();

    public function hideKeyboard(): bool;

    public function sendKeyboardMessage(
        string $text,
        array $buttons,
        bool $resize = true,
        bool $oneTime = false,
        bool $isTemporary = true,
        bool $noSaving = false,
        ?int $queueId = null
    ): mixed;

    public function deleteUserMessage(
        int $messageId,
        ?int $dbRowId = null
    ): bool;

    public function deleteSystemMessage(
        int $messageId,
        ?int $dbRowId = null
    ): bool;

    public function deleteUserMessages(): void;

    public function deleteSystemMessages(): void;

    public function deleteSystemMessagesByQueueId(
        ?int $queueId
    ): void;

    public function saveMessage(
        string $message,
        int|string|null $messageId,
        mixed $debug,
        string $type,
        bool $isTemporary = true
    ): bool;

    public function format(string $header, string $message, ?string $error = null): string;
}
