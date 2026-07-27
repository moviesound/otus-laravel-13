<?php

namespace Tests\Mocks\Bot;

use App\Contracts\Bot\Messengers\MessengerInterface;

class FakeMessenger implements MessengerInterface
{
    public array $messages = [];

    public function sendMessage(
        string $text,
        array $buttons = [],
        bool $isTemporary = true,
        bool $noSaving = false,
        ?int $queueId = null
    ): mixed {
        $this->messages[] = [
            'text' => $text,
            'buttons' => $buttons,
        ];

        return true;
    }


    public function name()
    {
        return 'fake';
    }


    public function hideKeyboard(): bool
    {
        return true;
    }


    public function sendKeyboardMessage(
        string $text,
        array $buttons,
        bool $resize = true,
        bool $oneTime = false,
        bool $isTemporary = true,
        bool $noSaving = false,
        ?int $queueId = null
    ): mixed {
        return $this->sendMessage(
            $text,
            $buttons,
            $isTemporary,
            $noSaving,
            $queueId
        );
    }


    public function deleteUserMessage(
        int $messageId,
        ?int $dbRowId = null
    ): bool {
        return true;
    }


    public function deleteSystemMessage(
        int $messageId,
        ?int $dbRowId = null
    ): bool {
        return true;
    }


    public function deleteUserMessages(): void
    {
    }


    public function deleteSystemMessages(): void
    {
    }


    public function deleteSystemMessagesByQueueId(
        ?int $queueId
    ): void {
    }


    public function saveMessage(
        string $message,
        int|string|null $messageId,
        mixed $debug,
        string $type,
        bool $isTemporary = true
    ): bool {
        return true;
    }


    public function format(
        string $header,
        string $message,
        ?string $error = null
    ): string {
        return $message;
    }
}
