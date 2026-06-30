<?php

namespace App\Services\Bot\TelegramWebhook;

use App\Contracts\Bot\Messengers\MessengerInterface;
use App\Contracts\Bot\Repositories\TelegramRepositoryInterface;
use App\Contracts\Bot\TelegramWebhook\TelegramGatewayInterface;

final class TelegramMessenger implements MessengerInterface
{

    private const NAME = 'telegram';

    public function __construct(
        private readonly TelegramGatewayInterface    $gateway,
        private readonly TelegramRepositoryInterface $repo,
        private readonly string|int                  $chatId,
        private readonly int                         $userId,
    )
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function sendMessage(
        string $text,
        array  $buttons = [],
        bool   $isTemporary = true,
        bool   $noSaving = false,
        ?int   $queueId = null
    ): mixed
    {
        $this->hideKeyboard();

        $payload = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => $buttons,
            ],
            'parse_mode' => 'HTML',
        ];

        $response = $this->gateway->post(
            '/sendMessage',
            $payload
        );

        $messageId = $response['result']['message_id'] ?? null;

        if ($messageId && !$noSaving) {
            $this->repo->saveSysMessage(
                $this->chatId,
                $messageId,
                $text,
                json_encode($payload),
                $isTemporary,
                $queueId
            );
        }

        return $response;
    }

    public function hideKeyboard(): bool
    {
        if (!$this->repo->needHideMessage(
            $this->chatId,
            $this->userId
        )) {
            return false;
        }

        $response = $this->gateway->post(
            '/sendMessage',
            [
                'chat_id' => $this->chatId,
                'reply_markup' => [
                    'remove_keyboard' => true,
                ],
            ]
        );

        return isset($response['result']['message_id']);
    }

    public function sendKeyboardMessage(
        string $text,
        array  $buttons,
        bool   $resize = true,
        bool   $oneTime = false,
        bool   $isTemporary = true,
        bool   $noSaving = false,
        ?int   $queueId = null
    ): mixed
    {
        $payload = [
            'chat_id' => $this->chatId,
            'text' => $text,
            'reply_markup' => [
                'keyboard' => $buttons,
                'resize_keyboard' => $resize,
                'one_time_keyboard' => $oneTime,
            ],
            'parse_mode' => 'HTML',
        ];

        $response = $this->gateway->post(
            '/sendMessage',
            $payload
        );

        $messageId = $response['result']['message_id'] ?? null;

        if ($messageId && !$noSaving) {
            $this->repo->saveSysMessage(
                $this->chatId,
                $messageId,
                $text,
                json_encode($payload),
                $isTemporary,
                $queueId
            );
        }

        return $response;
    }

    public function saveMessage(
        string          $message,
        int|string|null $messageId,
        mixed           $debug,
        string          $type,
        bool            $isTemporary = true
    ): bool
    {
        $this->repo->saveMessage(
            $this->chatId,
            $this->userId,
            $message,
            $messageId,
            $debug,
            $type,
            $isTemporary
        );

        return true;
    }

    public function deleteUserMessage(
        int|string $messageId,
        ?int       $dbRowId = null
    ): bool
    {
        $this->gateway->post(
            '/deleteMessage',
            [
                'chat_id' => $this->chatId,
                'message_id' => $messageId,
            ]
        );

        if ($dbRowId) {
            $this->repo->deleteUserRowById($dbRowId);
        }

        return true;
    }

    public function deleteSystemMessage(
        int|string $messageId,
        ?int       $dbRowId = null
    ): bool
    {
        $this->gateway->post(
            '/deleteMessage',
            [
                'chat_id' => $this->chatId,
                'message_id' => $messageId,
            ]
        );

        if ($dbRowId) {
            $this->repo->deleteSystemRowById($dbRowId);
        }

        return true;
    }

    public function deleteUserMessages(): void
    {
        $messages = $this->repo
            ->getAllUserMessagesOfChat($this->chatId);

        foreach ($messages as $message) {
            $this->deleteUserMessage(
                $message['message_id'],
                $message['id']
            );
        }
    }

    public function deleteSystemMessages(): void
    {
        $messages = $this->repo
            ->getAllSystemMessagesOfChat($this->chatId);

        foreach ($messages as $message) {
            $this->deleteSystemMessage(
                $message['message_id'],
                $message['id']
            );
        }
    }

    public function deleteSystemMessagesByQueueId(
        ?int $queueId
    ): void
    {
        $messages = $this->repo
            ->getSystemMessagesByQueueId($queueId);

        foreach ($messages as $message) {
            $this->deleteSystemMessage(
                $message['message_id'],
                $message['id']
            );
        }
    }

    public function format(string $header, string $message, ?string $error = null): string
    {
        $message = $this->headerAndMessage($header, $message);

        return $error
            ? $error . "\n\n" . $message
            : $message;
    }

    private function headerAndMessage(string $header, string $message): string
    {
        return $header . $message;
    }
}
