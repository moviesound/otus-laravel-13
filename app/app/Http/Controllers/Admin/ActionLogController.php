<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\ActionLogInterface;
use App\DTO\ActionLogSearchDTO;
use App\Http\Controllers\Admin\Requests\ActionLogSearchRequest;

class ActionLogController
{
    public function __construct(
        private ActionLogInterface $actionLogService
    ) {}

    public function index(ActionLogSearchRequest $request)
    {
        $data = $request->toDTOArray();

        $dto = new ActionLogSearchDTO(
            search: $data['search'] ?? null,
            userId: $data['userId'] ?? null,
            perPage: $data['perPage'] ?? 30,
        );

        $logs = $this->actionLogService
            ->getList($dto)
            ->appends($request->query());

        return view('logs.index', compact('logs'));
    }
}
