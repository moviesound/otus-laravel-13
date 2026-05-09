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

            @can('texts.view')
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
            @endcan

            @can('texts.create')
            <li>
                <button
                    type="button"
                    class="cursor-pointer flex items-center gap-3 rounded px-4 py-2 transition hover:bg-gray-800 w-full text-left"
                    @click="
            fetch('{{ route('texts.create') }}', {
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

                    $store.modal.openModal(data.html)
                })
        "
                >


                    └──
                    <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                        <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2"/>
                    </svg> Добавить текст
                </button>
            </li>
            @endcan

            @can('users.view')
                <li class="mt-4 border-t border-gray-800 pt-4">
                    <a
                        href="/users"
                        class="flex items-center gap-3 rounded px-4 py-2 transition hover:bg-gray-800"
                    >
                        <svg
                            class="h-5 w-5 fill-current"
                            viewBox="0 0 24 24"
                        >
                            <path d="M16 11c1.66 0 2.99-1.79 2.99-4S17.66 3 16 3s-3 1.79-3 4 1.34 4 3 4zm-8 0c1.66 0 2.99-1.79 2.99-4S9.66 3 8 3 5 4.79 5 7s1.34 4 3 4zm0 2c-2.33 0-7 1.17-7 3.5V20h14v-3.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V20h6v-3.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>

                        Админы
                    </a>
                </li>
            @endcan

            @can('users.create')
                <li>
                    <button
                        type="button"
                        class="cursor-pointer flex items-center gap-3 rounded px-4 py-2 transition hover:bg-gray-800 w-full text-left"
                        @click="
                        fetch('{{ route('admins.create') }}', {
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

                            $store.modal.openModal(data.html)
                        })
                    "
                    >

                        └──
                        <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2"/>
                        </svg>

                        Добавить админа
                    </button>
                </li>
            @endcan
        </ul>
    </nav>
</aside>
