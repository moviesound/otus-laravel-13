<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\SysTextInterface;
use App\Http\Controllers\Controller;

use App\DTO\SysTextSearchDTO;
use App\DTO\SysTextStoreDTO;
use App\DTO\SysTextUpdateDTO;
use App\Http\Controllers\Admin\Requests\SysTextSearchRequest;
use App\Http\Controllers\Admin\Requests\SysTextStoreRequest;
use App\Http\Controllers\Admin\Requests\SysTextUpdateRequest;
use App\Exceptions\DuplicateSysTextException;
use Illuminate\Http\Request;


class SysTextController extends Controller
{

    public function __construct(private SysTextInterface $sysTextService){}

    /**
     * Display a listing of the resource.
     */

    public function index(SysTextSearchRequest $request)
    {
        $data = $request->toDTOArray();

        $dto = new SysTextSearchDTO(
            alias: $data['alias'],
            lang: $data['lang'],
            perPage: $data['perPage'],
        );
        $texts = $this->sysTextService->getList($dto)
            ->appends($request->query());

        return view('texts.index', compact('texts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $langOptions = config('langs.options');

        return response()->json([
            'status' => 'ok',
            'html' => view('texts.partials.create', compact('langOptions'))->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SysTextStoreRequest $request)
    {
        $data = $request->toDTOArray();

        $dto = new SysTextStoreDTO(
            alias: $data['alias'],
            context: $data['context'],
            lang: $data['lang'],
        );

        $text = $this->sysTextService->storeRow($dto);

        return response()->json([
            'status' => 'ok',
            'message' => 'Запись успешно добавлена.
            После закрытия сообщения страница обновится,
            а запись появится в начале таблицы'
        ])->header('X-ITEM-ID', $text->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $text = $this->sysTextService->getRow($id);

        $langOptions = config('langs.options');

        if (!$text) {
            return response()->json([
                'status' => 'error',
                'message' => 'Запись не найдена'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'html' => view(
                'texts.partials.edit',
                compact('text', 'langOptions')
            )->render()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SysTextUpdateRequest $request, int $id)
    {
        $data = $request->toDTOArray();

        $dto = new SysTextUpdateDTO(
            id: $data['id'],
            alias: $data['alias'],
            context: $data['context'],
        );

        $this->sysTextService->updateRow($dto);

        return response()->json([
            'status' => 'ok',
            'message' => 'Запись успешно обновлена'
        ])->header('X-ITEM-ID', $data['id']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->sysTextService->deleteRow($id);

        return response()->json([
            'id' => $id,
            'status' => 'ok',
            'message' => 'Запись удалена'
        ])->header('X-ITEM-ID', $id);
    }
}
