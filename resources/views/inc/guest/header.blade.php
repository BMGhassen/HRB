<!DOCTYPE html>
<html lang="fr" class="h-full bg-lion-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B Grossiste | Établissement Rekik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('build/assets/img/007.png') }}" type="image/x-icon" />

    @include('inc.theme')
</head>
<body class="h-full font-sans antialiased text-white bg-lion-black">

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
                @if(Auth::user()->state === 'Actif')
                    <a href="{{ route('home') }}" class="hover:text-white transition-colors flex items-center gap-1.5 font-bold">
                        <i class="fa-solid fa-user"></i> Mon Espace
                    </a>
                @else
                    <a href="{{ url('/client/pending') }}" class="hover:text-white transition-colors flex items-center gap-1.5 font-bold">
                        <i class="fa-solid fa-user"></i> Statut du Compte
                    </a>
                @endif
            @else
                <a href="{{ url('/login') }}" class="hover:text-white transition-colors flex items-center gap-1.5 font-bold">
                    <i class="fa-solid fa-user"></i> Espace Pro
                </a>
            @endauth
            </div>
        </div>
    </div>

    {{-- Main Navbar --}}
    <header class="force-dark text-white bg-lion-card border-b border-zinc-800 sticky top-0 z-40 shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center justify-between gap-6">
                
                {{-- ── Frameless Large Lion Logo ───────────────────────── --}}
                <a href="{{ url('/') }}" class="flex-shrink-0 flex items-center gap-3.5 group">
                    <img src="{{ asset('build/assets/img/007.png') }}" 
                         alt="Logo Lion Ets. Habib Rekik" 
                         class="h-14 sm:h-16 w-auto object-contain transition-transform duration-300 group-hover:scale-105 filter drop-shadow-[0_0_15px_rgba(255,193,7,0.35)]">
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
                <form action="{{ route('guest.article.search') }}" method="GET" class="flex-1 max-w-2xl hidden md:block">
                    <div class="flex items-stretch bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden focus-within:border-[#FFC107] focus-within:ring-1 focus-within:ring-[#FFC107] transition-all">
                        <select name="category" class="bg-zinc-800 text-zinc-300 border-r border-zinc-700 px-4 py-3 text-sm font-bold focus:outline-none cursor-pointer hidden lg:block">
                            <option value="all">Toutes les Pièces</option>
                        </select>
                        <input type="text" name="search" placeholder="Rechercher référence SKU (ex: 001 AZ2011), Désignation..." required
                               class="w-full bg-transparent text-white placeholder-zinc-500 text-sm px-4 py-3 focus:outline-none">
                        <button type="submit" class="bg-[#FFC107] hover:bg-[#FFD54F] text-black font-black px-6 py-3 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    @auth
                        <span class="hidden sm:inline-block text-xs font-bold text-zinc-400 bg-zinc-900 px-3 py-1.5 rounded-lg border border-zinc-700">
                            {{ Auth::user()->name }}
                        </span>
                        @if(Auth::user()->state === 'Actif')
                            <a href="{{ route('home') }}" class="flex items-center gap-2 bg-gradient-to-r from-[#FFC107] to-[#E5A100] text-black font-black px-4 py-2.5 rounded-xl shadow-[0_4px_15px_rgba(255,193,7,0.3)] hover:brightness-110 active:scale-95 transition-all text-sm">
                                <i class="fa-solid fa-table-columns"></i> Tableau de bord
                            </a>
                        @else
                            <a href="{{ route('client.pending') }}" class="flex items-center gap-2 bg-gradient-to-r from-[#FFC107] to-[#E5A100] text-black font-black px-4 py-2.5 rounded-xl shadow-[0_4px_15px_rgba(255,193,7,0.3)] hover:brightness-110 active:scale-95 transition-all text-sm">
                                <i class="fa-solid fa-clock-rotate-left"></i> Statut
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center justify-center w-10 h-10 bg-zinc-900 hover:bg-red-500/10 text-zinc-400 hover:text-red-500 border border-zinc-700 hover:border-red-500/50 rounded-xl transition-all" title="Se déconnecter">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            </button>
                        </form>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="flex items-center gap-2 bg-zinc-900 hover:bg-zinc-800 text-white border border-zinc-700 px-4 py-2.5 rounded-xl text-sm font-bold transition-all hover:border-[#FFC107]">
                                <i class="fa-solid fa-right-to-bracket text-zinc-400"></i> Se Connecter
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="hidden sm:flex items-center gap-2 bg-gradient-to-r from-[#FFC107] to-[#E5A100] text-black font-black px-4 py-2.5 rounded-xl shadow-[0_4px_15px_rgba(255,193,7,0.3)] hover:brightness-110 active:scale-95 transition-all text-sm">
                                S'inscrire
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            {{-- Search Bar (phones: the one above is hidden below md) --}}
            <form action="{{ route('guest.article.search') }}" method="GET" class="md:hidden mt-3">
                <div class="flex items-stretch bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden focus-within:border-[#FFC107] focus-within:ring-1 focus-within:ring-[#FFC107] transition-all">
                    <input type="text" name="search" placeholder="Référence SKU, désignation..." required
                           class="w-full bg-transparent text-white placeholder-zinc-500 text-sm px-4 py-2.5 focus:outline-none">
                    <button type="submit" class="bg-[#FFC107] hover:bg-[#FFD54F] text-black font-black px-4 transition-colors" aria-label="Rechercher">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </form>
        </div>
        
        {{-- Category Bar --}}
        <div class="bg-zinc-900/90 border-t border-zinc-800/80 px-4 py-2.5">
            <div class="max-w-7xl mx-auto flex items-center justify-between text-xs gap-4 overflow-x-auto">
                <nav class="flex items-center gap-6 text-zinc-300 font-semibold whitespace-nowrap">
                    <a href="{{ route('guest.article.search') }}" class="text-lion-accent hover:underline flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group"></i> Tout le Catalogue
                    </a>
                    <a href="#" class="text-amber-400 hover:text-amber-300 font-bold">Devenir Client Pro</a>
                </nav>
            </div>
        </div>
    </header>

</body>
</html>