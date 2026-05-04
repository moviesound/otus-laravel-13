<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\SysTextInterface;
use App\Http\Controllers\Controller;
use App\Objects\SysTextSearchObject;
use App\Objects\SysTextStoreObject;
use App\Objects\SysTextUpdateObject;
use Illuminate\Http\Request;

class SysTextController extends Controller
{

    public function __construct(
        private SysTextInterface $sysTextService
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(?Request $request)
    {
        $langs = config('langs.list', ['ru']);

        $searchObject = SysTextSearchObject::create([
            'alias' => $request->input('alias', null),
            'lang' => $request->input('lang', null),
            'perPage' => $request->input('per_page', 20)
        ], $langs);

        $texts = $this->sysTextService->getList($searchObject)
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
    public function store(Request $request)
    {
        $langs = config('langs.list', ['ru']);

        $storeObject = SysTextStoreObject::create([
            'alias' => $request->input('alias'),
            'context' => $request->input('context'),
            'lang' => $request->input('lang'),
        ], $langs);

        $isSuccess = $this->sysTextService->storeRow($storeObject);

        if ($isSuccess === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Такой alias существует'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Запись успешно добавлена. После закрытия сообщения
            страница обновится, а запись появится в начале таблицы'
        ]);
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
    public function update(Request $request, int $id)
    {
        $updateObject = SysTextUpdateObject::create([
            'id' => $id,
            'alias' => $request->alias,
            'context' => $request->context
        ]);

        $isSuccess = $this->sysTextService->updateRow($updateObject);

        if ($isSuccess === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Запись не найдена'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Запись успешно обновлена. После закрытия сообщения
            страница обновится и запись перенесётся в начало таблицы'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $isDeleted = $this->sysTextService->deleteRow($id);

        if ($isDeleted === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Запись не найдена'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Запись удалена'
        ]);
    }
}
