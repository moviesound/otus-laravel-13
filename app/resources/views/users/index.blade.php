@extends('layouts.admin')

@section('content')

    <form method="GET" class="mb-4 flex items-end gap-4">

        <div class="flex flex-col">
            <label class="mb-1 text-sm text-black dark:text-white">
                Имя
            </label>

            <input
                type="text"
                name="name"
                value="{{ request('name') }}"
                placeholder="Поиск по имени"
                class="h-10 rounded border border-stroke bg-white px-3 text-sm
                dark:border-strokedark dark:bg-boxdark dark:text-white"
            >
        </div>

        <div class="flex flex-col">
            <label class="mb-1 text-sm text-black dark:text-white">
                Почта
            </label>

            <input
                type="text"
                name="email"
                value="{{ request('email') }}"
                placeholder="Поиск по почте"
                class="h-10 rounded border border-stroke bg-white px-3 text-sm
                dark:border-strokedark dark:bg-boxdark dark:text-white"
            >
        </div>

        <div class="flex gap-2 items-end">

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
                    Дата изменения
                </th>

                <th class="min-w-[180px] px-4 py-4 font-medium text-black dark:text-white">
                    Имя
                </th>

                <th class="min-w-[220px] px-4 py-4 font-medium text-black dark:text-white">
                    Почта
                </th>

                <th class="min-w-[220px] px-4 py-4 font-medium text-black dark:text-white">
                    Роль
                </th>

                <th class="min-w-[220px] px-4 py-4 font-medium text-black dark:text-white">
                    Права
                </th>

                @canany(['users.update', 'users.password_change', 'users.delete'])
                    <th class="px-4 py-4 font-medium text-black dark:text-white">
                        Действия
                    </th>
                @endcanany

            </tr>
            </thead>

            <tbody>

            @foreach ($admins as $user)

                <tr class="border-b border-stroke dark:border-strokedark">

                    <td class="px-4 py-5">
                        {{ optional($user->updated_at)->format('Y-m-d') }}
                    </td>

                    <td class="px-4 py-5">
                        {{ $user->name }}
                    </td>

                    <td class="px-4 py-5">
                        {{ $user->email }}
                    </td>

                    <td class="px-4 py-5">

                        <div class="flex flex-col gap-2">
                            <div class="flex flex-wrap gap-2">
                                @foreach($user->roles as $role)
                                    <span class="rounded bg-gray-200 px-2 py-1 text-xs dark:bg-gray-700">
                                        role: {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>


                        </div>

                    </td>

                    <td class="px-4 py-5">

                        <div class="flex flex-col gap-2">
                            <div class="flex flex-wrap gap-2">
                                @foreach($user->getAllPermissions() as $permission)
                                    <span class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-700 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                            </div>

                        </div>

                    </td>

                    @canany(['users.update', 'users.password_change', 'users.delete'])

                        <td class="px-4 py-5">

                            <div x-data="{ open: false }" class="relative">

                                {{-- BUTTON --}}
                                <button
                                    @click="open = !open"
                                    class="cursor-pointer rounded-md bg-gray-200 px-3 py-1.5
                                    text-sm font-medium hover:bg-gray-300 dark:bg-gray-700
                                    dark:hover:bg-gray-600"
                                >
                                    Действия
                                </button>

                                {{-- DROPDOWN --}}
                                <div
                                    x-show="open"
                                    x-transition
                                    @click.outside="open = false"
                                    class="absolute right-0 mt-2 z-50 flex w-56 flex-col rounded-md
                                    border border-gray-200 bg-white shadow-lg
                                    dark:border-gray-700 dark:bg-gray-800"
                                >

                                    {{-- EDIT --}}
                                    @can('users.update')

                                        <a
                                            href="{{ route('admins.edit', $user->id) }}"
                                            @click.prevent="

                                            apiFetch($el.href)
                                                .then(({ res, data }) => {

                                                    if (!res.ok) {
                                                        $store.modal.showAlert(data.message || 'У вас нет прав')
                                                        return
                                                    }

                                                    $store.modal.openModal(data.html)
                                                })
                                            "
                                            class="w-full px-4 py-2 text-left text-sm
                                            hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            Редактировать
                                        </a>

                                    @endcan

                                    {{-- PASSWORD --}}
                                    @can('users.password_change')

                                        <form
                                            method="POST"
                                            action="{{ route('admins.password', $user->id) }}"
                                            @submit.prevent="$store.modal.submit($event)"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="cursor-pointer w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                            >
                                                Сбросить пароль
                                            </button>
                                        </form>
                                    @endcan

                                    {{-- DELETE --}}
                                    @can('users.delete')

                                        <form
                                            method="POST"
                                            action="{{ route('admins.delete', $user->id) }}"
                                            @submit.prevent="$store.modal.delete($event, {{ $user->id }})"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="cursor-pointer w-full px-4 py-2 text-left
                                                text-sm text-red-500 hover:bg-gray-100
                                                dark:hover:bg-gray-700"
                                            >
                                                Удалить
                                            </button>

                                        </form>

                                    @endcan

                                </div>

                            </div>

                        </td>

                    @endcanany

                </tr>

            @endforeach

            {{-- PAGINATION --}}
            <tr>
                <td colspan="5" class="px-4 py-4">

                    <div class="mt-4 flex justify-center">
                        {{ $admins->links('vendor.pagination.tailwind-smart') }}
                    </div>

                </td>
            </tr>

            </tbody>

        </table>

    </div>

@endsection
