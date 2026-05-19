@extends('layouts.admin')

@section('content')

    {{-- FILTER --}}
    <form method="GET" class="mb-4 flex items-end gap-4">

        {{-- USER ID FILTER --}}
        <div class="flex flex-col">
            <label class="mb-1 text-sm text-black dark:text-white">
                User ID
            </label>

            <input
                type="number"
                name="user_id"
                value="{{ request('user_id') }}"
                placeholder="Фильтр по пользователю"
                class="h-10 rounded border border-stroke bg-white px-3 text-sm
                   dark:border-strokedark dark:bg-boxdark dark:text-white"
            >
        </div>

        {{-- SEARCH FILTER --}}
        <div class="flex flex-col">
            <label class="mb-1 text-sm text-black dark:text-white">
                Action
            </label>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Поиск по action"
                class="h-10 rounded border border-stroke bg-white px-3 text-sm
                   dark:border-strokedark dark:bg-boxdark dark:text-white"
            >
        </div>

        {{-- BUTTONS --}}
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

    {{-- TABLE --}}
    <div
        class="overflow-visible rounded-sm border border-stroke bg-white shadow-default
        dark:border-strokedark dark:bg-boxdark">

        <table class="w-full table-auto">

            <thead>
            <tr class="bg-gray-2 text-left dark:bg-meta-4">

                <th class="min-w-[180px] px-4 py-4 font-medium text-black dark:text-white">
                    Дата
                </th>

                <th class="min-w-[100px] px-4 py-4 font-medium text-black dark:text-white">
                    User ID
                </th>

                <th class="min-w-[300px] px-4 py-4 font-medium text-black dark:text-white">
                    Action
                </th>

                <th class="min-w-[120px] px-4 py-4 font-medium text-black dark:text-white">
                    IP
                </th>

                <th class="min-w-[250px] px-4 py-4 font-medium text-black dark:text-white">
                    User Agent
                </th>

            </tr>
            </thead>

            <tbody>

            @foreach ($logs as $log)

                <tr class="border-b border-stroke dark:border-strokedark">

                    {{-- DATE --}}
                    <td class="px-4 py-5">
                        {{ optional($log->created_at)->format('Y-m-d H:i:s') }}
                    </td>

                    {{-- USER --}}
                    <td class="px-4 py-5">
                        {{ $log->user_id }}
                    </td>

                    {{-- ACTION --}}
                    <td class="px-4 py-5 font-mono text-xs">
                        {{ $log->action }}
                    </td>

                    {{-- IP --}}
                    <td class="px-4 py-5">
                        {{ $log->ip }}
                    </td>

                    {{-- USER AGENT --}}
                    <td class="px-4 py-5 text-xs">
                        {{ \Illuminate\Support\Str::limit($log->user_agent, 60) }}
                    </td>

                </tr>

            @endforeach

            {{-- PAGINATION --}}

            <tr>
                <td colspan="5" class="px-4 py-4">
                    <div class="mt-4 flex justify-center">
                        {{ $logs->links('vendor.pagination.tailwind-smart') }}
                    </div>
                </td>
            </tr>

            </tbody>

        </table>

    </div>

@endsection
