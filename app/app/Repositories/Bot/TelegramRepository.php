<?php

namespace App\Repositories\Bot;

use App\Contracts\Bot\Repositories\TelegramRepositoryInterface;
use App\Models\Bot\TelegramAnswer;
use App\Models\Bot\TelegramSysMessage;
use App\Models\Bot\UserSocial;

class TelegramRepository implements TelegramRepositoryInterface
{
    public function saveMessage(
        int|string|null $chatId,
        int|string|null $userId,
        string          $message,
        int|string|null $messageId,
        mixed           $debug,
        string          $type,
        bool            $isTemporary = true
    ): void
    {
        TelegramAnswer::query()->create([
            'user_id' => $userId,
            'chat_id' => $chatId,
            'message' => $message,
            'message_id' => $messageId,
            'debug' => $debug,
            'type' => $type,
            'status' => 0,
            'is_temporary' => $isTemporary,
        ]);
    }

    public function saveSysMessage(
        int|string|null $chatId,
        int|string|null $messageId,
        string          $message,
        string          $query,
        bool            $isTemporary,
        ?int            $queueId = null
    ): void
    {
        TelegramSysMessage::query()->create([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'message' => $message,
            'query' => $query,
            'is_temporary' => $isTemporary,
            'queue_id' => $queueId,
        ]);
    }

    public function getFirst10Messages(string $daemon): array
    {
        $now = now();

        TelegramAnswer::query()
            ->where('status', 0)
            ->whereIn('type', ['user', 'callback'])
            ->where(function ($q) use ($now) {
                $q->whereNull('locked_at')
                    ->orWhere('locked_at', '<', $now->subMinutes(5));
            })
            ->orderBy('date_add')
            ->limit(10)
            ->update([
                'locked_by' => $daemon,
                'locked_at' => now(),
            ]);

        return TelegramAnswer::query()
            ->where('locked_by', $daemon)
            ->where('status', 0)
            ->orderBy('date_add')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getAllUserMessagesOfChat(string|int $chatId): array
    {
        return TelegramAnswer::query()
            ->byChatId($chatId)
            ->byType('user')
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    public function getAllSystemMessagesOfChat(string|int $chatId): array
    {
        return TelegramSysMessage::query()
            ->byChatId($chatId)
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    public function deleteUserRowById(int $id): void
    {
        TelegramAnswer::query()
            ->where('id', $id)
            ->delete();
    }

    public function deleteSystemRowById(int $id): void
    {
        TelegramSysMessage::query()
            ->where('id', $id)
            ->delete();
    }

    public function getSystemMessagesByQueueId(?int $queueId): array
    {
        return TelegramSysMessage::query()
            ->byQueueId($queueId)
            ->get()
            ->toArray();
    }

    public function needHideMessage(string|int $chatId, int $userId): int
    {
        return UserSocial::query()
            ->where('user_id', $userId)
            ->where('type', 'telegram')
            ->where('id', $chatId)
            ->value('keyboard') ?? 0;
    }
}
