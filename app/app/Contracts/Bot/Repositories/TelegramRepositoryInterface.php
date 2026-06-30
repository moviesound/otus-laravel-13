<?php

namespace App\Contracts\Bot\Repositories;

interface TelegramRepositoryInterface
{
    public function saveMessage(
        int|string|null $chatId,
        int|string|null $userId,
        string          $message,
        int|string|null $messageId,
        mixed           $debug,
        string          $type,
        bool            $isTemporary = true
    ): void;

    public function saveSysMessage(
        int|string|null $chatId,
        int|string|null $messageId,
        string          $message,
        string          $query,
        bool            $isTemporary,
        ?int            $queueId = null
    ): void;

    public function getFirst10Messages(string $daemon): array;

    public function getAllUserMessagesOfChat(
        string|int $chatId
    ): array;

    public function getAllSystemMessagesOfChat(
        string|int $chatId
    ): array;

    public function deleteUserRowById(int $id): void;

    public function deleteSystemRowById(int $id): void;

    public function getSystemMessagesByQueueId(
        ?int $queueId
    ): array;

    public function needHideMessage(
        string|int $chatId,
        int        $userId
    ): int;
}
