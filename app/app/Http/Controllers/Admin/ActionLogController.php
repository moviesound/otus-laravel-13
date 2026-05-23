<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\ActionLogInterface;
use App\Http\Controllers\Admin\Requests\ActionLogSearchRequest;

class ActionLogController
{
    public function __construct(
        private ActionLogInterface $actionLogService
    ) {}

    public function index(ActionLogSearchRequest $request)
    {
        $dto = $request->toDTO();

        $logs = $this->actionLogService
            ->getList($dto)
            ->appends($request->query());

        return view('logs.index', compact('logs'));
    }
}
