@include('inc.guest.header', ['searchTerm' => $searchTerm])

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-black text-white">Catalogue Pièces</h2>
        <div class="flex items-center gap-4">
            <span class="text-zinc-500 text-sm font-bold">{{ $results->total() }} résultats</span>
            @guest
            <a href="{{ route('login') }}" class="bg-[#FFC107] hover:bg-[#FFD54F] text-black font-black px-4 py-2 rounded-xl text-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Connexion Pro
            </a>
            @endguest
        </div>
    </div>

    {{-- B2B Login CTA Banner (only for visitors who are not logged in) --}}
    @guest
    <div class="bg-lion-card border border-[#FFC107]/30 rounded-2xl p-5 mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[#FFC107]/10 border border-[#FFC107]/30 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-lock text-lion-accent text-xl"></i>
            </div>
            <div>
                <p class="text-white font-bold">Accédez aux prix et à la commande en ligne</p>
                <p class="text-zinc-400 text-sm">Connectez-vous à votre espace client B2B pour voir les prix et passer commande.</p>
            </div>
        </div>
        <a href="{{ route('login') }}" class="flex-shrink-0 bg-gradient-to-r from-[#FFC107] to-[#E5A100] text-black font-black px-6 py-3 rounded-xl shadow-[0_4px_15px_rgba(255,193,7,0.3)] hover:brightness-110 transition-all whitespace-nowrap">
            Se connecter →
        </a>
    </div>
    @endguest

    @if ($results->isEmpty())
        <div class="text-center py-20 bg-lion-card border border-zinc-800 rounded-2xl">
            <i class="fa-solid fa-magnifying-glass text-4xl text-zinc-600 mb-4 block"></i>
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
                            <span class="text-emerald-400 bg-emerald-950/60 border border-emerald-800/50 text-[11px] font-bold px-2 py-0.5 rounded-full">En Stock</span>
                        @elseif($r->qte_stock > 0)
                            <span class="text-amber-400 bg-amber-950/60 border border-amber-800/50 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $r->qte_stock }} restants</span>
                        @else
                            <span class="text-red-400 bg-red-950/60 border border-red-800/50 text-[11px] font-bold px-2 py-0.5 rounded-full">Épuisé</span>
                        @endif
                    </div>

                    <a href="{{ route('guest.details', ['id' => $r->id]) }}" class="block h-36 mb-4 bg-[#fff] rounded-xl p-2 flex items-center justify-center overflow-hidden">
                        <img class="max-h-full object-contain mix-blend-multiply" src="{{ asset('build/assets/img/' . $r->ref . '.jpg') }}" onerror="this.src='{{ asset('build/shopassets/img/logo_Header.png') }}'" alt="{{ $r->ref }}">
                    </a>

                    <a href="{{ route('guest.details', ['id' => $r->id]) }}" class="block font-bold text-white group-hover:text-lion-accent transition-colors mb-2 text-sm line-clamp-2">
                        {{ $r->designation }}
                    </a>
                </div>

                <div class="mt-4">
                    <div class="flex items-center justify-between pt-3 border-t border-zinc-800 mb-3">
                        <span class="text-xs text-zinc-500 italic">Prix réservé aux pros</span>
                        <i class="fa-solid fa-lock text-zinc-600 text-xs"></i>
                    </div>
                    <a href="{{ route('login') }}" class="block w-full text-center bg-zinc-800 hover:bg-zinc-700 text-white font-bold py-2.5 rounded-lg text-sm transition-colors border border-zinc-700 hover:border-[#FFC107]">
                        <i class="fa-solid fa-lock mr-1 text-lion-accent"></i> Voir le prix
                    </a>
                </div>

            </div>
            @endforeach
        </div>

        <div class="mt-10 flex justify-center custom-pagination">
            {{ $results->links() }}
        </div>
        <style>
            .custom-pagination nav > div:first-child { display: none; }
            .custom-pagination svg { width: 1.25rem; height: 1.25rem; color: rgb(var(--lion-accent)); }
            .custom-pagination span[aria-current="page"] > span { background: #FFC107 !important; color: black !important; border-color: #FFC107 !important; }
            .custom-pagination a { background: rgb(var(--lion-card)) !important; color: rgb(var(--white)) !important; border-color: rgb(var(--zinc-800)) !important; }
            .custom-pagination a:hover { background: #FFC107 !important; color: black !important; }
        </style>
    @endif

</main>

@include('inc.guest.footer')
