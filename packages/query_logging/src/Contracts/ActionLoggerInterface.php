<?php

namespace Packages\QueryLogging\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\QueryLogging\DTO\ActionLogSearchDTO;

interface ActionLoggerInterface
{
    public function getList(ActionLogSearchDTO $dto): LengthAwarePaginator;

    public function store(string $action, int $userId, ?string $ip = null, ?string $userAgent = null);
}
