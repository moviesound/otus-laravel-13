<form
    method="POST"
    action="{{ route('texts.update', $text->id) }}"
    @submit.prevent="$store.modal.submit($event)"
    class="space-y-4"
>
    @csrf
    @method('PUT')

    {{-- LANG --}}
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            Язык
        </label>

        <select
            name="lang"
            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm
                   focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >
            @foreach($langOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('lang', $text->lang) === $value)>
                    {{ $label }} ({{ $value }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- ALIAS --}}
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            Alias
        </label>

        <input
            type="text"
            name="alias"
            value="{{ old('alias', $text->alias) }}"
            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm
                   focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >
    </div>

    {{-- CONTEXT --}}
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            Текст
        </label>

        <textarea
            name="context"
            rows="6"
            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm
                   focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >{{ old('context', $text->context) }}</textarea>
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
            Сохранить
        </button>
    </div>

</form>
