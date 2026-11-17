@include('inc.client.header', ['searchTerm' => $searchTerm ?? ''])

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    {{-- Alerts --}}
    @if(session('success'))
    <div class="mb-6 bg-emerald-900/30 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3">
        <i class="fa-solid fa-circle-check"></i>
        <p class="text-sm font-bold">{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 bg-red-900/30 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl flex items-center gap-3">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <p class="text-sm font-bold">{{ session('error') }}</p>
    </div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-black text-white">Catalogue Pièces</h2>
        <span class="text-zinc-500 text-sm font-bold">{{ $results->total() }} résultats</span>
    </div>

    @if ($results->isEmpty())
        <div class="text-center py-20 bg-lion-card border border-zinc-800 rounded-2xl">
            <i class="fa-solid fa-magnifying-glass text-4xl text-zinc-600 mb-4"></i>
            <h5 class="text-zinc-400 font-bold">Aucun article trouvé.</h5>
            <p class="text-zinc-500 text-sm mt-2">Veuillez vérifier votre recherche ou essayer une autre référence.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($results as $index => $r)
            <div class="bg-lion-card border border-zinc-800 hover:border-[#FFC107]/60 rounded-2xl p-5 transition-all shadow-xl hover:shadow-[0_0_25px_rgba(255,193,7,0.15)] group flex flex-col justify-between">
                
                <div>
                    <div class="flex justify-between items-start mb-3">
                        <span class="bg-black text-[#FFC107] font-mono font-bold text-xs px-2.5 py-1 rounded-lg border border-[#FFC107]/40">
                            {{ $r->ref }}
                        </span>
                        @if($r->qte_stock > 10)
                            <span class="text-emerald-400 bg-emerald-950/60 border border-emerald-800/50 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                En Stock
                            </span>
                        @elseif($r->qte_stock > 0)
                            <span class="text-amber-400 bg-amber-950/60 border border-amber-800/50 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                {{ (int) $r->qte_stock }} {{ $r->qte_stock > 1 ? 'restants' : 'restant' }}
                            </span>
                        @else
                            <span class="text-red-400 bg-red-950/60 border border-red-800/50 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                Épuisé
                            </span>
                        @endif
                    </div>
                    
                    <a href="{{ route('details', ['id' => $r->id]) }}" class="block h-36 mb-4 bg-[#fff] rounded-xl p-2 flex items-center justify-center overflow-hidden">
                        <img class="max-h-full object-contain mix-blend-multiply" src="{{ asset('build/assets/img/' . $r->ref . '.jpg') }}" onerror="this.src='{{ asset('build/shopassets/img/logo_Header.png') }}'" alt="{{ $r->ref }}">
                    </a>

                    <a href="{{ route('details', ['id' => $r->id]) }}" class="block font-bold text-white group-hover:text-lion-accent transition-colors mb-2 text-sm line-clamp-2">
                        {{ $r->designation }}
                    </a>
                </div>
                
                <div class="mt-4">
                    <div class="flex items-baseline justify-between pt-3 border-t border-zinc-800 mb-4">
                        <span class="text-xs text-zinc-400">Prix Pro HT</span>
                        <span class="text-xl font-black text-white">{{ $r->getPrice() }} <small class="text-xs text-lion-accent">DT</small></span>
                    </div>

                    <form action="{{ route('commande.store') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="idarticle" value="{{ $r->id }}">
                        <input type="number" name="qte" value="1" min="1" max="{{ $r->qte_stock > 0 ? $r->qte_stock : 1 }}" class="w-16 bg-zinc-900 border border-zinc-700 rounded-lg text-white text-center text-sm focus:outline-none focus:border-[#FFC107]">
                        <button type="submit" class="flex-1 bg-[#FFC107] hover:bg-[#FFD54F] text-black font-bold py-2 rounded-lg text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed" @if($r->qte_stock <= 0) disabled @endif>
                            Ajouter
                        </button>
                    </form>
                </div>

            </div>
            @endforeach
        </div>

        <div class="mt-10 flex justify-center custom-pagination">
            {{ $results->links() }}
        </div>
        
        <style>
            /* Quick overriding of Laravel pagination for dark theme */
            .custom-pagination nav > div:first-child { display: none; }
            .custom-pagination svg { width: 1.25rem; height: 1.25rem; color: rgb(var(--lion-accent)); }
            .custom-pagination span[aria-current="page"] > span { background: #FFC107 !important; color: black !important; border-color: #FFC107 !important; }
            .custom-pagination a { background: rgb(var(--lion-card)) !important; color: rgb(var(--white)) !important; border-color: rgb(var(--zinc-800)) !important; }
            .custom-pagination a:hover { background: #FFC107 !important; color: black !important; }
        </style>
    @endif

</main>

@include('inc.client.footer')
