<!DOCTYPE html>
<html lang="fr" class="h-full bg-lion-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B Grossiste | Établissement Rekik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('build/shopassets/img/logo_Header.png') }}" type="image/x-icon" />

    @include('inc.theme')
</head>
<body class="h-full font-sans antialiased text-white bg-lion-black" x-data="{ userDropdown: false, cartDropdown: false }">

    {{-- Top B2B Utility Bar --}}
    <div class="force-dark bg-lion-ink/90 border-b border-zinc-800 text-[12px] text-zinc-400 py-1.5 px-4">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5 text-zinc-300">
                    <i class="fa-solid fa-truck-fast text-lion-accent"></i>
                    <span>Livraison Express <strong>24/48h</strong> sur toute la Tunisie</span>
                </span>
                <span class="hidden md:inline text-zinc-600">|</span>
                <span class="hidden md:flex items-center gap-1.5">
                    <i class="fa-solid fa-phone text-lion-accent"></i>
                    <span>Service Pro : <strong class="text-white">+216 71 000 000</strong></span>
                </span>
            </div>

            <div class="flex items-center gap-3">
                @include('inc.theme-toggle')
            @auth
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 bg-zinc-900 border border-zinc-700/80 px-3 py-0.5 rounded-full text-[11px]">
                    <span class="text-zinc-400">Encours B2B :</span>
                    <span class="text-emerald-400 font-bold font-mono">
                        {{ number_format(Auth::user()->client?->encours ?? 0, 3, ',', ' ') }} DT
                    </span>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="hover:text-white transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
            @endauth
            </div>
        </div>
    </div>

    {{-- Main Navbar --}}
    <header class="force-dark text-white bg-lion-card border-b border-zinc-800 sticky top-0 z-40 shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between gap-6">
                
                {{-- ── Frameless Large Lion Logo ───────────────────────── --}}
                <a href="{{ url('/') }}" class="flex-shrink-0 flex items-center gap-3.5 group">
                    {{-- Big Lion Head without any frame/box --}}
                    <img src="{{ asset('build/assets/img/007.png') }}" 
                         alt="Logo Lion Ets. Habib Rekik" 
                         class="h-14 sm:h-16 w-auto object-contain transition-transform duration-300 group-hover:scale-105 filter drop-shadow-[0_0_15px_rgba(255,193,7,0.35)]">
                    
                    {{-- Typography aligned with the bigger logo --}}
                    <div class="hidden sm:block">
                        <h1 class="text-white font-black text-xl sm:text-2xl tracking-tight leading-none group-hover:text-lion-accent transition-colors">
                            ETS. HABIB <span class="text-lion-accent">REKIK</span>
                        </h1>
                        <span class="text-lion-accent-light font-bold text-[11px] tracking-[0.16em] uppercase block mt-1">
                            Pièces Auto en Gros
                        </span>
                    </div>
                </a>

                {{-- Search Bar --}}
                <form action="{{ route('client.article.search') }}" method="GET" class="flex-1 max-w-2xl hidden md:block">
                    <div class="flex items-stretch bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden focus-within:border-[#FFC107] focus-within:ring-1 focus-within:ring-[#FFC107] transition-all">
                        <select name="category" class="bg-zinc-800 text-zinc-300 border-r border-zinc-700 px-4 py-3 text-sm font-bold focus:outline-none cursor-pointer hidden lg:block">
                            <option value="all">Toutes les Pièces</option>
                        </select>
                        <input type="text" name="search" placeholder="Rechercher référence SKU (ex: 001 AZ2011), Désignation..." value="{{ $searchTerm ?? '' }}" required
                               class="w-full bg-transparent text-white placeholder-zinc-500 text-sm px-4 py-3 focus:outline-none">
                        <button type="submit" class="bg-[#FFC107] hover:bg-[#FFD54F] text-black font-black px-6 py-3 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    @auth
                    {{-- User Dropdown --}}
                    <div class="relative" @click.away="userDropdown = false">
                        <button @click="userDropdown = !userDropdown" 
                                class="flex items-center gap-2.5 p-2 sm:px-3.5 sm:py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-200 hover:text-white hover:border-[#FFC107] transition-all">
                            <div class="w-7 h-7 rounded-lg bg-[#FFC107] text-black flex items-center justify-center font-bold text-xs">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div class="hidden sm:block text-left text-xs leading-tight">
                                <span class="text-[10px] text-zinc-400 block">Espace Client</span>
                                <span class="font-bold text-white">{{ Auth::user()->name }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-zinc-400 ml-1"></i>
                        </button>

                        <div x-show="userDropdown" x-transition style="display: none;"
                             class="absolute right-0 mt-2 w-56 bg-zinc-900 border border-zinc-700 rounded-2xl shadow-2xl py-2 z-50 text-xs">
                            <div class="px-4 py-2 border-b border-zinc-800">
                                <p class="text-zinc-400 text-[10px] uppercase font-bold">Connecté en tant que :</p>
                                <p class="font-bold text-white truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('client.commandes') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-zinc-300 hover:bg-zinc-800 hover:text-lion-accent">
                                <i class="fa-solid fa-file-invoice text-zinc-400"></i> Mes Factures & Commandes
                            </a>
                        </div>
                    </div>
                    @endauth

                    {{-- Pro Cart Button --}}
                    <a href="{{ route('client.panier') }}" class="relative flex items-center gap-2.5 bg-gradient-to-r from-[#FFC107] to-[#E5A100] text-black font-black px-4 py-2.5 rounded-xl shadow-[0_4px_15px_rgba(255,193,7,0.3)] hover:brightness-110 active:scale-95 transition-all">
                        <i class="fa-solid fa-cart-shopping text-base"></i>
                        <span class="hidden sm:inline text-xs">Panier</span>
                        @php
                            $cartCount = 0;
                            if (Auth::check()) {
                                $commande = \App\Models\Commande::where('client_id', Auth::id())->where('etat', 'Brouillon')->first();
                                if ($commande) {
                                    $cartCount = $commande->ligneCommandes->count();
                                }
                            }
                        @endphp
                        <span class="bg-black text-[#FFC107] font-extrabold text-[11px] px-2 py-0.5 rounded-full border border-black/40">{{ $cartCount }}</span>
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Category Bar --}}
        <div class="bg-zinc-900/90 border-t border-zinc-800/80 px-4 py-2.5">
            <div class="max-w-7xl mx-auto flex items-center justify-between text-xs gap-4 overflow-x-auto">
                <nav class="flex items-center gap-6 text-zinc-300 font-semibold whitespace-nowrap">
                    <a href="{{ route('client.article.search') }}" class="text-lion-accent hover:underline flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group"></i> Tout le Catalogue
                    </a>
                    <a href="#" class="hover:text-lion-accent">Nouveautés</a>
                </nav>
            </div>
        </div>
    </header>
