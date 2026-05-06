<aside class="flex h-screen w-72 flex-col overflow-y-auto bg-black text-white">
    <!-- HEADER -->
    <div class="flex items-center justify-between px-6 py-5">
        <div class="text-lg font-semibold">
            Меню
        </div>
    </div>

    <!-- MENU -->
    <nav class="px-4 py-4" x-data="{ selected: 'Texts' }">
        <ul class="flex flex-col gap-2">

            <!-- ONLY ITEM -->
            <li>
                <a
                    href="/texts"
                    class="flex items-center gap-3 rounded px-4 py-2 transition hover:bg-gray-800"
                    :class="selected === 'Texts' ? 'bg-gray-800' : ''"
                >
                    <!-- icon -->
                    <svg
                        class="h-5 w-5 fill-current"
                        viewBox="0 0 24 24"
                    >
                        <path d="M4 6h16v2H4V6zm0 5h16v2H4v-2zm0 5h10v2H4v-2z"/>
                    </svg>

                    Тексты
                </a>
            </li>
            <li>
                <button
                    type="button"
                    class="cursor-pointer flex items-center gap-3 rounded px-4 py-2 transition hover:bg-gray-800 w-full text-left"
                    @click="
            fetch('{{ route('texts.create') }}')
                .then(r => r.json())
                .then(data => $store.modal.openModal(data.html))
        "
                >


                    └──
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                        <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2"/>
                    </svg> Добавить текст
                </button>
            </li>

        </ul>
    </nav>
</aside>
