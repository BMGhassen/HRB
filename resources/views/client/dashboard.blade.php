@include('inc.client.header')

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="relative overflow-hidden rounded-3xl border border-zinc-800 bg-gradient-to-r from-zinc-950 via-lion-card to-zinc-950 p-8 sm:p-12 mb-10 shadow-2xl">
            <div class="relative z-10 max-w-2xl">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#FFC107]/10 text-lion-accent border border-[#FFC107]/30 mb-4">
                    <i class="fa-solid fa-crown"></i> Distributeur Officiel Pièces Détachées
                </span>
                <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight mb-4">
                    La Puissance du Stock <span class="text-lion-accent">Automobile en Gros</span>
                </h1>
                <p class="text-zinc-400 text-sm sm:text-base mb-6">
                    Accédez à plus de <strong>47 000 références</strong> en direct de notre ERP iDempiere. Tarifs préférentiels pour garages et revendeurs.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('client.article.search') }}" class="bg-[#FFC107] hover:bg-[#FFD54F] text-black font-black px-6 py-3 rounded-xl shadow-[0_4px_20px_rgba(255,193,7,0.35)] transition-all flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-layer-group"></i> Explorer le Catalogue
                    </a>
                </div>
            </div>
        </div>

        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i class="fa-solid fa-fire text-lion-accent"></i> Produits Récents
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($produits as $p)
            <div class="bg-lion-card border border-zinc-800 hover:border-[#FFC107]/60 rounded-2xl p-5 transition-all shadow-xl hover:shadow-[0_0_25px_rgba(255,193,7,0.15)] group flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-3">
                        <span class="bg-black text-[#FFC107] font-mono font-bold text-xs px-2.5 py-1 rounded-lg border border-[#FFC107]/40">
                            {{ $p->ref }}
                        </span>
                        @if($p->qte_stock > 10)
                            <span class="text-emerald-400 bg-emerald-950/60 border border-emerald-800/50 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                En Stock
                            </span>
                        @elseif($p->qte_stock > 0)
                            <span class="text-amber-400 bg-amber-950/60 border border-amber-800/50 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                {{ (int) $p->qte_stock }} {{ $p->qte_stock > 1 ? 'restants' : 'restant' }}
                            </span>
                        @else
                            <span class="text-red-400 bg-red-950/60 border border-red-800/50 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                Épuisé
                            </span>
                        @endif
                    </div>
                    
                    <div class="h-32 mb-4 bg-[#fff] rounded-xl p-2 flex items-center justify-center overflow-hidden">
                        <img class="max-h-full object-contain mix-blend-multiply" src="{{ asset('build/assets/img/' . $p->ref . '.jpg') }}" onerror="this.src='{{ asset('build/shopassets/img/logo_Header.png') }}'" alt="{{ $p->ref }}">
                    </div>

                    <h4 class="font-bold text-white group-hover:text-lion-accent transition-colors mb-2 text-sm line-clamp-2">
                        {{ $p->designation }}
                    </h4>
                </div>
                
                <div class="mt-4">
                    <div class="flex items-baseline justify-between pt-3 border-t border-zinc-800 mb-4">
                        <span class="text-xs text-zinc-400">Prix Pro HT</span>
                        <span class="text-xl font-black text-white">{{ number_format($p->prix, 3, ',', ' ') }} <small class="text-xs text-lion-accent">DT</small></span>
                    </div>

                    <form action="{{ route('commande.store') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="idarticle" value="{{ $p->id }}">
                        <input type="number" name="qte" value="1" min="1" max="{{ $p->qte_stock > 0 ? $p->qte_stock : 1 }}" class="w-16 bg-zinc-900 border border-zinc-700 rounded-lg text-white text-center text-sm focus:outline-none focus:border-[#FFC107]">
                        <button type="submit" class="flex-1 bg-[#FFC107] hover:bg-[#FFD54F] text-black font-bold py-2 rounded-lg text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed" @if($p->qte_stock <= 0) disabled @endif>
                            Ajouter
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </main>

@include('inc.client.footer')


