<?php

namespace App\Contracts;

use App\DTO\ActionLogSearchDTO;
use App\Models\Admin\ActionLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ActionLogInterface
{
    public function getList(ActionLogSearchDTO $dto): LengthAwarePaginator;

    public function store(string $action, int $userId, ?string $ip = null, ?string $userAgent = null): ActionLog;
}
