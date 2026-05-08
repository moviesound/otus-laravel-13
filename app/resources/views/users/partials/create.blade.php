<form
    method="POST"
    action="{{ route('admins.store') }}"
    @submit.prevent="$store.modal.submit($event)"
    class="space-y-4"
>
    @csrf

    {{-- NAME --}}
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            Имя
        </label>

        <input
            type="text"
            name="name"
            value="{{ old('name') }}"
            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm
                   focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            placeholder="Введите имя"
        >
    </div>

    {{-- EMAIL --}}
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            Email
        </label>

        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm
                   focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            placeholder="Введите email"
        >
    </div>

    {{-- PASSWORD (optional for create) --}}
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            Пароль
        </label>

        <input
            type="password"
            name="password"
            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm
                   focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            placeholder="Введите пароль"
        >
    </div>

    {{-- ROLES --}}
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            Роли
        </label>

        <select
            name="roles[]"
            multiple
            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm
                   focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >
            @foreach($roles as $role)
                <option value="{{ $role->name }}">
                    {{ $role->name }}
                </option>
            @endforeach
        </select>

        <p class="mt-1 text-xs text-gray-500">
            Можно выбрать несколько ролей
        </p>
    </div>

    {{-- ACTIONS --}}
    <div class="flex justify-end gap-2 pt-2">

        <button
            type="button"
            @click="$store.modal.close()"
            class="rounded bg-gray-200 px-4 py-2 text-sm hover:bg-gray-300
                   dark:bg-gray-700 dark:hover:bg-gray-600"
        >
            Отмена
        </button>

        <button
            type="submit"
            class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700"
        >
            Создать
        </button>
    </div>

</form>
