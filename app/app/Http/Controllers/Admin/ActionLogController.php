<?php

namespace App\Http\Controllers\Admin;

use App\DTO\ActionLogSearchDTO;
use App\DTO\ActionLogStoreDTO;
use App\Http\Controllers\Admin\Requests\AdminLogSearchRequest;
use App\Http\Controllers\Admin\Requests\AdminLogStoreRequest;
use App\Http\Controllers\Controller;
use App\Repositories\ActionLogRepository;

class ActionLogController extends Controller
{
    public function index(AdminLogSearchRequest $request)
    {
        $data = $request->toDTOArray();

        $dto = new ActionLogSearchDTO(
            search: $data['search'],
            userId: $data['userId'],
            perPage: $data['perPage'],
        );

        $logs = ActionLogRepository::getListWithPagination($dto);

        return view('admin.logs', compact('logs'));
    }

    public function store(AdminLogStoreRequest $request)
    {
        $data = $request->toDTOArray();

        $dto = new ActionLogStoreDTO(
            userId: $data['userId'],
            action: $data['action'],
            ip: $data['ip'],
            userAgent: $data['userAgent'],
        );

        $log = ActionLogRepository::storeRow($dto);

        return response()->json([
            'status' => 'ok',
            'log_id' => $log->_id,
        ]);
    }
}
