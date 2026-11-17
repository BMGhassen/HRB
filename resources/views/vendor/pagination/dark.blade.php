{{-- Dark, French pagination for the Tailwind pages (admin and shop). --}}
@if ($paginator->hasPages())
    @php
        $base = 'min-w-[2.25rem] h-9 px-3 inline-flex items-center justify-center rounded-lg text-sm font-semibold border transition-colors';
        $idle = $base . ' bg-zinc-900 border-zinc-800 text-zinc-300 hover:border-[#FFC107]/60 hover:text-[#FFC107]';
        $off  = $base . ' bg-zinc-900/50 border-zinc-800/50 text-zinc-600 cursor-not-allowed';
        $on   = $base . ' bg-[#FFC107] border-[#FFC107] text-black';
    @endphp
    <nav role="navigation" aria-label="Pagination" class="flex items-center gap-1.5 flex-wrap">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="{{ $off }}" aria-disabled="true"><i class="fa-solid fa-chevron-left text-xs"></i><span class="hidden sm:inline ml-2">Précédent</span></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $idle }}"><i class="fa-solid fa-chevron-left text-xs"></i><span class="hidden sm:inline ml-2">Précédent</span></a>
        @endif

        {{-- Page numbers (desktop) --}}
        <div class="hidden md:flex items-center gap-1.5">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1 text-zinc-600">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="{{ $on }}" aria-current="page">{{ number_format($page, 0, ',', ' ') }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $idle }}" aria-label="Page {{ $page }}">{{ number_format($page, 0, ',', ' ') }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Current page (mobile) --}}
        <span class="md:hidden px-2 text-sm text-zinc-400">
            Page <strong class="text-white">{{ $paginator->currentPage() }}</strong> / {{ $paginator->lastPage() }}
        </span>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $idle }}"><span class="hidden sm:inline mr-2">Suivant</span><i class="fa-solid fa-chevron-right text-xs"></i></a>
        @else
            <span class="{{ $off }}" aria-disabled="true"><span class="hidden sm:inline mr-2">Suivant</span><i class="fa-solid fa-chevron-right text-xs"></i></span>
        @endif
    </nav>
@endif
