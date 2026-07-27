<?php

namespace Packages\QueryLogging\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\QueryLogging\DTO\ActionLogSearchDTO;
use Packages\QueryLogging\Models\ActionLog;

class ActionLogRepository
{
    public static function getListWithPagination(
        ActionLogSearchDTO $dto
    ): LengthAwarePaginator {

        return ActionLog::query()
            ->when($dto->userId, fn ($q) => $q->where('user_id', (int) $dto->userId))
            ->when($dto->search, fn ($q) => $q->where('action', 'like', "%{$dto->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate($dto->perPage);
    }

    public static function storeRow(
        string $action,
        int $userId,
        ?string $ip = null,
        ?string $userAgent = null
    ): ActionLog {
        return ActionLog::create([
            'user_id' => $userId,
            'action' => $action,
            'ip' => $ip ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
