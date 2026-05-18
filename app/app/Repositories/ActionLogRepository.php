<?php

namespace App\Repositories;

use App\DTO\ActionLogSearchDTO;
use App\DTO\ActionLogStoreDTO;
use App\Models\Admin\ActionLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActionLogRepository
{
    /**
     * Список логов с фильтрами
     */
    public static function getListWithPagination(
        ActionLogSearchDTO $dto
    ): LengthAwarePaginator {
        return ActionLog::query()
            ->when(
                $dto->userId,
                fn ($q) => $q->where('user_id', $dto->userId)
            )
            ->when(
                $dto->search,
                fn ($q) => $q->whereRaw([
                    '$text' => [
                        '$search' => $dto->search
                    ]
                ])
            )
            ->latest('created_at')
            ->paginate($dto->perPage)
            ->onEachSide(2);
    }

    /**
     * Добавление лога
     */
    public static function storeRow(
        ActionLogStoreDTO $dto
    ): ActionLog {
        return ActionLog::create([
            'user_id' => $dto->userId,
            'action' => $dto->action,
            'ip' => $dto->ip ?? request()->ip(),
            'user_agent' => $dto->userAgent ?? request()->userAgent(),
            'created_at' => now()
        ]);
    }

    /**
     * Удаление лога
     */
    public static function deleteRow(string $id): bool
    {
        $log = ActionLog::find($id);

        if (!$log) {
            return false;
        }

        return (bool) $log->delete();
    }

    /**
     * Очистка старых логов
     */
    public static function clearOldLogs(int $days = 365): int
    {
        return ActionLog::where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
