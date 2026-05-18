<?php

namespace App\Repositories;

use App\DTO\ActionLogSearchDTO;
use App\Models\Admin\ActionLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActionLogRepository
{
    public static function getListWithPagination(
        ActionLogSearchDTO $dto
    ): LengthAwarePaginator {
        return ActionLog::query()
            ->when($dto->userId, fn ($q) => $q->where('user_id', $dto->userId))
            ->when($dto->search, fn ($q) => $q->where('action', 'like', "%{$dto->search}%"))
            ->orderByDesc('created_at')
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
