@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();

    $start = max(1, $current - 2);
    $end = min($last, $current + 2);
@endphp

<nav class="flex items-center gap-1">

    {{-- PREV --}}
    @if ($current > 1)
        <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1">‹</a>
    @endif

    {{-- FIRST 3 --}}
    @for ($i = 1; $i <= min(3, $last); $i++)
        <a href="{{ $paginator->url($i) }}"
           class="px-3 py-1 {{ $current == $i ? 'font-bold text-primary' : '' }}">
            {{ $i }}
        </a>
    @endfor

    {{-- LEFT ELLIPSIS --}}
    @if ($start > 4)
        <span class="px-2">...</span>
    @endif

    {{-- MIDDLE (без пересечения с first/last) --}}
    @for ($i = max(4, $start); $i <= min($end, $last - 3); $i++)
        <a href="{{ $paginator->url($i) }}"
           class="px-3 py-1 {{ $current == $i ? 'font-bold text-primary' : '' }}">
            {{ $i }}
        </a>
    @endfor

    {{-- RIGHT ELLIPSIS --}}
    @if ($end < $last - 3)
        <span class="px-2">...</span>
    @endif

    {{-- LAST 3 (строго без пересечений) --}}
    @for ($i = max($last - 2, 1); $i <= $last; $i++)
        @if ($i > 3)
            <a href="{{ $paginator->url($i) }}"
               class="px-3 py-1 {{ $current == $i ? 'font-bold text-primary' : '' }}">
                {{ $i }}
            </a>
        @endif
    @endfor

    {{-- NEXT --}}
    @if ($current < $last)
        <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1">›</a>
    @endif

</nav>
