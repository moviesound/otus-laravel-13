<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin')</title>

    @vite(['resources/css/app.css',  'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body
    x-data="{ page: 'tables', 'loaded': true, 'darkMode': true, 'stickyMenu': false, 'sidebarToggle': true, 'scrollTop': false }"
    x-init="
          darkMode = JSON.parse(localStorage.getItem('darkMode'));
          $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark text-bodydark bg-boxdark-2': darkMode === true}"
>

<div class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    {{-- Main area --}}
    <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">

        {{-- Header --}}
        @include('layouts.header')

        {{-- Page content --}}
        <main>
            <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                @yield('content')
            </div>
        </main>

    </div>
</div>

<div
    x-show="$store.modal.open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
>
    <div class="w-full max-w-2xl rounded-lg bg-white p-6 dark:bg-boxdark relative">

        <button
            @click="$store.modal.close()"
            class="cursor-pointer absolute right-3 top-3 text-gray-500 hover:text-black dark:hover:text-white"
        >
            ✕
        </button>

        <div x-html="$store.modal.content"></div>

    </div>
</div>
<div
    x-show="$store.modal.alertOpen"
    x-cloak
    x-transition
    class="fixed inset-0 flex items-center justify-center z-50"
>
    <div class="absolute inset-0 bg-black/50"
         @click="$store.modal.closeAlert()"></div>

    <div class="relative bg-primary rounded-xl shadow-lg p-6 w-full max-w-sm text-center">
        <p class="mb-4 text-white" x-text="$store.modal.alertMessage"></p>

        <button
            class="cursor-pointer px-4 py-2 bg-blue-600 text-white rounded"
            @click="$store.modal.closeAlert()"
        >
            OK
        </button>
    </div>
</div>
<div
    x-show="$store.modal.confirmOpen"
    x-cloak
    x-transition
    class="fixed inset-0 flex items-center justify-center z-50"
>
    <!-- overlay -->
    <div
        class="absolute inset-0 bg-black/50"
        @click="$store.modal.closeConfirm()"
    ></div>

    <!-- modal -->
    <div class="relative bg-primary rounded-xl shadow-lg p-6 w-full max-w-sm text-center">

        <p class="mb-6 text-white" x-text="$store.modal.confirmMessage"></p>

        <div class="flex justify-center gap-3">

            <button
                class="cursor-pointer px-4 py-2 bg-gray-600 text-white rounded"
                @click="$store.modal.closeConfirm()"
            >
                Отмена
            </button>

            <button
                class="cursor-pointer px-4 py-2 bg-gray-800 text-white rounded"
                @click="
                    if ($store.modal.confirmAction) $store.modal.confirmAction();
                    $store.modal.closeConfirm();
                "
            >
                Подтвердить
            </button>

        </div>
    </div>
</div>
</body>
</html>
