<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Requests\ActionLogSearchRequest;
use App\Models\Admin\ActionLog;

class ActionLogController
{
    public function index(ActionLogSearchRequest $request)
    {
        $logs = ActionLog::query()
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('logs.index', compact('logs'));
    }
}
