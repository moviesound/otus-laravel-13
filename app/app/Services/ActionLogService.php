<?php

namespace App\Services;

use App\Contracts\ActionLogInterface;
use App\DTO\ActionLogSearchDTO;
use App\Models\Admin\ActionLog;
use App\Repositories\ActionLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActionLogService implements ActionLogInterface
{
    public function getList(ActionLogSearchDTO $dto): LengthAwarePaginator
    {
        return ActionLogRepository::getListWithPagination($dto);
    }

    public function store(
        string $action,
        int $userId,
        ?string $ip = null,
        ?string $userAgent = null
    ): ActionLog {
        return ActionLogRepository::storeRow(
            action: $action,
            userId: $userId,
            ip: $ip,
            userAgent: $userAgent
        );
    }
}
