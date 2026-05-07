@extends('layouts.admin')

@section('content')
    <form method="GET" class="mb-4 flex items-end gap-4">

        {{-- ФИЛЬТР: LANG --}}
        <div class="flex flex-col justify-end items-end">
            <label class="mb-1 text-sm text-black dark:text-white">
                Язык
            </label>

            <select
                name="lang"
                class="h-10 rounded border border-stroke bg-white px-3 text-sm
                   dark:border-strokedark dark:bg-boxdark dark:text-white"
            >
                @foreach (config('langs.options') as $key => $label)
                    <option value="{{ $key }}" @selected(request('lang') === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ФИЛЬТР: ALIAS --}}
        <div class="flex flex-col">
            <label class="mb-1 text-sm text-black dark:text-white">
                Alias
            </label>

            <input
                type="text"
                name="alias"
                value="{{ request('alias') }}"
                placeholder="Поиск по alias"
                class="h-10 rounded border border-stroke bg-white px-3 text-sm
                   dark:border-strokedark dark:bg-boxdark dark:text-white"
            >
        </div>

        {{-- КНОПКИ --}}
        <div class="flex gap-2" style="align-items: end;">

            <button
                class="h-10 rounded bg-blue-600 px-4 text-sm text-white hover:bg-blue-700"
                type="submit"
            >
                Фильтр
            </button>

            <a
                href="{{ url()->current() }}"
                class="flex h-10 items-center rounded bg-gray-200 px-4 text-sm
                   hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
            >
                Сброс
            </a>

        </div>

    </form>

    <div
        class="overflow-visible rounded-sm border border-stroke bg-white shadow-default
        dark:border-strokedark dark:bg-boxdark">
        <table class="w-full table-auto">
            <thead>
            <tr class="bg-gray-2 text-left dark:bg-meta-4">
                <th class="min-w-[150px] px-4 py-4 font-medium text-black dark:text-white">
                    Дата изменений
                </th>
                <th class="min-w-[120px] px-4 py-4 font-medium text-black dark:text-white">
                    Alias
                </th>
                <th class="min-w-[120px] px-4 py-4 font-medium text-black dark:text-white">
                    Язык
                </th>
                <th class="min-w-[300px] px-4 py-4 font-medium text-black dark:text-white">
                    Текст
                </th>
<<<<<<< HEAD
                <th class="px-4 py-4 font-medium text-black dark:text-white">
                    Действия
                </th>
=======
                @canany(['texts.update', 'texts.delete'])
                <th class="px-4 py-4 font-medium text-black dark:text-white">
                    Действия
                </th>
                @endcanany
>>>>>>> 3431310 (add first part)
            </tr>
            </thead>

            <tbody>

            @foreach ($texts as $text)
                <tr class="border-b border-stroke dark:border-strokedark">

                    <td class="px-4 py-5">
                        {{ optional($text->updated_at)->format('Y-m-d') }}
                    </td>

                    <td class="px-4 py-5">
                        {{ $text->alias }}
                    </td>

                    <td class="px-4 py-5">
                        {{ $text->lang }}
                    </td>

                    <td class="px-4 py-5">
                        {{ $text->context }}
                    </td>
<<<<<<< HEAD

=======
                    @canany(['texts.update', 'texts.delete'])
>>>>>>> 3431310 (add first part)
                    <td class="px-4 py-5">
                        <div x-data="{ open: false }" class="relative">

                            <!-- кнопка -->
                            <button
                                @click="open = !open"
                                class="cursor-pointer rounded-md bg-gray-200 px-3 py-1.5
                                text-sm font-medium hover:bg-gray-300 dark:bg-gray-700
                                dark:hover:bg-gray-600"
                            >
                                Действия
                            </button>

                            <!-- dropdown -->
                            <div
                                x-show="open"
                                x-transition
                                @click.outside="open = false"
                                class="absolute right-0 mt-2 w-56 flex flex-col rounded-md
                                border border-gray-200 bg-white shadow-lg dark:border-gray-700
                                dark:bg-gray-800 z-50"
                            >
<<<<<<< HEAD
                                <a
                                    href="{{ route('texts.edit', $text->id) }}"
                                    @click.prevent="
                                        fetch($el.href)
                                        .then(r => r.json())
                                        .then(data => {
=======
                                @can('texts.update')
                                <a
                                    href="{{ route('texts.edit', $text->id) }}"
                                    @click.prevent="
                                        fetch($el.href, {
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'Accept': 'application/json',
                                            }
                                        })
                                        .then(async (r) => {
                                            const data = await r.json()

                                            if (!r.ok) {
                                                $store.modal.showAlert(data.message || 'У вас нет прав')
                                                return
                                            }

>>>>>>> 3431310 (add first part)
                                            $store.modal.openModal(data.html)
                                        })
                                    "
                                    class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100
                                    dark:hover:bg-gray-700"
                                >
                                    Редактировать
                                </a>
<<<<<<< HEAD

=======
                                @endcan

                                @can('texts.delete')
>>>>>>> 3431310 (add first part)
                                <form
                                    method="POST"
                                    action="{{ route('texts.delete', $text->id) }}"
                                    @submit.prevent="$store.modal.delete($event, {{ $text->id }})"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="cursor-pointer w-full px-4 py-2 text-left text-sm text-red-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    >
                                        Удалить
                                    </button>
                                </form>
<<<<<<< HEAD
=======
                                @endcan
>>>>>>> 3431310 (add first part)
                            </div>

                        </div>
                    </td>
<<<<<<< HEAD

=======
                    @endcanany
>>>>>>> 3431310 (add first part)
                </tr>
            @endforeach

            <tr>
                <td colspan="5" class="px-4 py-4">
                    <div class="mt-4 flex justify-center">
                        {{ $texts->links('vendor.pagination.tailwind-smart') }}
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
