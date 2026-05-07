<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\SysTextInterface;
use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\DTO\SysTextSearchDTO;
use App\DTO\SysTextStoreDTO;
use App\DTO\SysTextUpdateDTO;
use App\Http\Controllers\Admin\Requests\SysTextSearchRequest;
use App\Http\Controllers\Admin\Requests\SysTextStoreRequest;
use App\Http\Controllers\Admin\Requests\SysTextUpdateRequest;
=======
use App\Objects\SysTextSearchObject;
use App\Objects\SysTextStoreObject;
use App\Objects\SysTextUpdateObject;
use Illuminate\Http\Request;
>>>>>>> 3431310 (add first part)

class SysTextController extends Controller
{

<<<<<<< HEAD
    public function __construct(
        private SysTextInterface $sysTextService
    )
    {
    }
=======
    public function __construct(private SysTextInterface $sysTextService){}
>>>>>>> 3431310 (add first part)

    /**
     * Display a listing of the resource.
     */
<<<<<<< HEAD
    public function index(SysTextSearchRequest $request)
    {
        $data = $request->toDTOArray();

        $dto = new SysTextSearchDTO(
            alias: $data['alias'],
            lang: $data['lang'],
            perPage: $data['perPage'],
        );

        $texts = $this->sysTextService->getList($dto)
=======
    public function index(?Request $request)
    {
        $langs = config('langs.list', ['ru']);

        $searchObject = SysTextSearchObject::create([
            'alias' => $request->input('alias', null),
            'lang' => $request->input('lang', null),
            'perPage' => $request->input('per_page', 20)
        ], $langs);

        $texts = $this->sysTextService->getList($searchObject)
>>>>>>> 3431310 (add first part)
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
<<<<<<< HEAD
    public function store(SysTextStoreRequest $request)
    {
        $data = $request->toDTOArray();

        $dto = new SysTextStoreDTO(
            alias: $data['alias'],
            context: $data['context'],
            lang: $data['lang'],
        );

        $isSuccess = $this->sysTextService->storeRow($dto);
=======
    public function store(Request $request)
    {
        $langs = config('langs.list', ['ru']);

        $storeObject = SysTextStoreObject::create([
            'alias' => $request->input('alias'),
            'context' => $request->input('context'),
            'lang' => $request->input('lang'),
        ], $langs);

        $isSuccess = $this->sysTextService->storeRow($storeObject);
>>>>>>> 3431310 (add first part)

        if ($isSuccess === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Такой alias существует'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
<<<<<<< HEAD
            'message' => 'Запись успешно добавлена. После закрытия сообщения страница обновится,
            а запись появится в начале таблицы'
=======
            'message' => 'Запись успешно добавлена. После закрытия сообщения
            страница обновится, а запись появится в начале таблицы'
>>>>>>> 3431310 (add first part)
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
<<<<<<< HEAD
    public function update(SysTextUpdateRequest $request, int $id)
    {
        $data = $request->toDTOArray();

        $dto = new SysTextUpdateDTO(
            id: $data['id'],
            alias: $data['alias'],
            context: $data['context'],
        );

        $isSuccess = $this->sysTextService->updateRow($dto);
=======
    public function update(Request $request, int $id)
    {
        $updateObject = SysTextUpdateObject::create([
            'id' => $id,
            'alias' => $request->alias,
            'context' => $request->context
        ]);

        $isSuccess = $this->sysTextService->updateRow($updateObject);
>>>>>>> 3431310 (add first part)

        if ($isSuccess === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Запись не найдена'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
<<<<<<< HEAD
            'message' => 'Запись успешно обновлена'
=======
            'message' => 'Запись успешно обновлена. После закрытия сообщения
            страница обновится и запись перенесётся в начало таблицы'
>>>>>>> 3431310 (add first part)
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
