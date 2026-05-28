<?php

namespace Packages\QueryLogging\Http\Controllers;

use Packages\QueryLogging\Contracts\ActionLoggerInterface;
use Packages\QueryLogging\Http\Controllers\Requests\ActionLogSearchRequest;

class ActionLogController
{
    public function __construct(
        private ActionLoggerInterface $actionLogService
    ) {}

    public function index(ActionLogSearchRequest $request)
    {
        $dto = $request->toDTO();

        $logs = $this->actionLogService
            ->getList($dto)
            ->appends($request->query());

        return view('query_logging::logging', compact('logs'));
    }
}
