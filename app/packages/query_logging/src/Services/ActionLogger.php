<?php

namespace Packages\QueryLogging\Services;


use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\QueryLogging\Contracts\ActionLoggerInterface;
use Packages\QueryLogging\DTO\ActionLogSearchDTO;
use Packages\QueryLogging\Models\ActionLog;
use Packages\QueryLogging\Repositories\ActionLogRepository;

class ActionLogger implements ActionLoggerInterface
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
