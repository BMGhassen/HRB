<!DOCTYPE html>
<html lang="fr" class="h-full bg-[#0A0D17]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B Grossiste | Établissement Rekik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('build/assets/img/logo_lion.png') }}" type="image/x-icon" />

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        lion: {
                            light: '#FFD54F',
                            DEFAULT: '#FFC107', // Exact Lion Gold
                            dark: '#E5A100',
                            black: '#0A0D17',
                            card: '#141824',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-slate-100 bg-[#0A0D17]" x-data="{ userDropdown: false, cartCount: 3 }">

    {{-- ── 1. Top B2B Utility Bar ────────────────────────────────────────── --}}
    <div class="bg-black/90 border-b border-zinc-800 text-[12px] text-zinc-400 py-1.5 px-4">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5 text-zinc-300">
                    <i class="fa-solid fa-truck-fast text-[#FFC107]"></i>
                    <span>Livraison Express <strong>24/48h</strong> sur toute la Tunisie</span>
                </span>
                <span class="hidden md:inline text-zinc-600">|</span>
                <span class="hidden md:flex items-center gap-1.5">
                    <i class="fa-solid fa-phone text-[#FFC107]"></i>
                    <span>Service client : <strong class="text-white">+216 71 000 000</strong></span>
                </span>
            </div>

            {{-- B2B Credit Balance Pill --}}
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 bg-zinc-900 border border-zinc-700/80 px-3 py-0.5 rounded-full text-[11px]">
                    <span class="text-zinc-400">Encours B2B :</span>
                    <span class="font-bold text-[#FFC107]">4 200,000 DT</span>
                    <span class="text-zinc-600">/</span>
                    <span class="text-emerald-400 font-semibold">5 800 DT dispo</span>
                </div>
                <a href="{{ url('/admin/erp/status') }}" class="text-[#FFC107] hover:underline font-bold text-[11px] flex items-center gap-1">
                    <i class="fa-solid fa-bolt"></i> Synchro ERP
                </a>
            </div>
        </div>
    </div>

    {{-- ── 2. Main Navigation Header (Logo, Search, Actions) ─────────────── --}}
    <header class="sticky top-0 z-40 bg-[#0A0D17]/95 backdrop-blur-md border-b border-zinc-800/80 shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4 lg:gap-8">

                {{-- Brand Logo with Golden Lion Emblem --}}
                <a href="{{ url('/articles') }}" class="flex items-center gap-3.5 group flex-shrink-0">
                    <div class="w-12 h-12 rounded-2xl bg-black border-2 border-[#FFC107] flex items-center justify-center shadow-[0_0_20px_rgba(255,193,7,0.35)] transition-all group-hover:scale-105">
                        <img src="{{ asset('build/assets/img/logo_lion.png') }}" 
                             onerror="this.onerror=null; this.src='https://placehold.co/40x40/000000/FFC107?text=🦁';"
                             alt="Logo" class="w-9 h-9 object-contain">
                    </div>
                    <div>
                        <div class="text-white font-black tracking-wider text-lg leading-tight">
                            ÉTABLISSEMENT <span class="text-[#FFC107]">REKIK</span>
                        </div>
                        <div class="text-[#FFD54F] text-[10px] font-bold tracking-[0.18em] uppercase">
                            Pièces Auto en Gros
                        </div>
                    </div>
                </a>

                {{-- Wide Smart Search Bar --}}
                <form action="{{ url('/articles') }}" method="GET" class="hidden md:flex flex-1 max-w-2xl relative">
                    <div class="w-full flex items-center bg-zinc-900 border-2 border-zinc-700 rounded-2xl overflow-hidden transition-all focus-within:border-[#FFC107] focus-within:shadow-[0_0_20px_rgba(255,193,7,0.25)]">
                        <select name="category" class="bg-zinc-800 text-zinc-300 text-xs font-semibold px-3 py-3 border-r border-zinc-700 focus:outline-none cursor-pointer">
                            <option value="">Toutes catégories</option>
                            <option value="Électrique">Électrique</option>
                            <option value="Freinage">Freinage</option>
                            <option value="Filtration">Filtration</option>
                        </select>

                        <input type="text" name="q" placeholder="Rechercher référence SKU (ex: 001 AZ2011), Code OEM, Désignation..."
                               class="w-full bg-transparent text-white placeholder-zinc-500 text-sm px-4 py-3 focus:outline-none">

                        <button type="submit" class="bg-[#FFC107] hover:bg-[#FFD54F] text-black font-black px-6 py-3 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>

                {{-- Header Actions (Quick Order, Account, Cart) --}}
                <div class="flex items-center gap-3">
                    <button class="hidden lg:flex items-center gap-2 bg-zinc-900 hover:bg-zinc-800 text-[#FFC107] border border-zinc-700 px-3.5 py-2.5 rounded-xl text-xs font-extrabold transition-all hover:border-[#FFC107]">
                        <i class="fa-solid fa-bolt"></i> Commande Rapide
                    </button>

                    {{-- User Dropdown --}}
                    <div class="relative" @click.away="userDropdown = false">
                        <button @click="userDropdown = !userDropdown" 
                                class="flex items-center gap-2.5 p-2 sm:px-3.5 sm:py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-200 hover:text-white hover:border-[#FFC107] transition-all">
                            <div class="w-7 h-7 rounded-lg bg-[#FFC107] text-black flex items-center justify-center font-bold text-xs">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div class="hidden sm:block text-left text-xs leading-tight">
                                <span class="text-[10px] text-zinc-400 block">Espace Client</span>
                                <span class="font-bold text-white">Garage Habib</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-zinc-400 ml-1"></i>
                        </button>

                        <div x-show="userDropdown" x-transition 
                             class="absolute right-0 mt-2 w-56 bg-zinc-900 border border-zinc-700 rounded-2xl shadow-2xl py-2 z-50 text-xs">
                            <div class="px-4 py-2 border-b border-zinc-800">
                                <p class="text-zinc-400 text-[10px] uppercase font-bold">Connecté en tant que :</p>
                                <p class="font-bold text-white truncate">contact@garage-habib.tn</p>
                            </div>
                            <a href="#" class="flex items-center gap-2.5 px-4 py-2.5 text-zinc-300 hover:bg-zinc-800 hover:text-[#FFC107]">
                                <i class="fa-solid fa-file-invoice text-zinc-400"></i> Mes Factures & BL
                            </a>
                            <a href="{{ url('/admin/erp/status') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-zinc-300 hover:bg-zinc-800 hover:text-[#FFC107]">
                                <i class="fa-solid fa-server text-zinc-400"></i> Dashboard ERP Sync
                            </a>
                        </div>
                    </div>

                    {{-- Cart Button --}}
                    <button class="relative flex items-center gap-2.5 bg-gradient-to-r from-[#FFC107] to-[#E5A100] text-black font-black px-4 py-2.5 rounded-xl shadow-[0_4px_15px_rgba(255,193,7,0.3)] hover:brightness-110 active:scale-95 transition-all">
                        <i class="fa-solid fa-cart-shopping text-base"></i>
                        <span class="hidden sm:inline text-xs">Panier</span>
                        <span class="bg-black text-[#FFC107] font-extrabold text-[11px] px-2 py-0.5 rounded-full border border-black/40" x-text="cartCount"></span>
                    </button>
                </div>

            </div>
        </div>

        {{-- ── 3. Category & Vehicle Selector Bar ────────────────────────── --}}
        <div class="bg-zinc-900/90 border-t border-zinc-800/80 px-4 py-2.5">
            <div class="max-w-7xl mx-auto flex items-center justify-between text-xs gap-4 overflow-x-auto">
                <button class="flex items-center gap-2 bg-[#FFC107]/10 border border-[#FFC107]/40 text-[#FFC107] hover:bg-[#FFC107] hover:text-black font-bold px-3 py-1.5 rounded-lg transition-all flex-shrink-0">
                    <i class="fa-solid fa-car-side"></i>
                    <span>Sélectionner un Véhicule</span>
                    <i class="fa-solid fa-chevron-down text-[9px] ml-1"></i>
                </button>

                <nav class="flex items-center gap-6 text-zinc-300 font-semibold whitespace-nowrap">
                    <a href="{{ url('/articles') }}" class="text-[#FFC107] hover:underline flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group"></i> Tout le Catalogue (47k)
                    </a>
                    <a href="#" class="hover:text-[#FFC107]">Électricité & Allumage</a>
                    <a href="#" class="hover:text-[#FFC107]">Système de Freinage</a>
                    <a href="#" class="hover:text-[#FFC107]">Filtres & Vidange</a>
                    <a href="#" class="hover:text-[#FFC107]">Refroidissement</a>
                    <a href="#" class="text-amber-400 hover:text-amber-300 font-bold">🔥 Déstockage (-30%)</a>
                </nav>
            </div>
        </div>
    </header>

    {{-- ── Mock Hero Section ─────────────────────────────────────────────── --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="relative overflow-hidden rounded-3xl border border-zinc-800 bg-gradient-to-r from-zinc-950 via-[#141824] to-zinc-950 p-8 sm:p-12 mb-10 shadow-2xl">
            <div class="relative z-10 max-w-2xl">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#FFC107]/10 text-[#FFC107] border border-[#FFC107]/30 mb-4">
                    <i class="fa-solid fa-crown"></i> Distributeur Officiel Pièces Détachées
                </span>
                <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight mb-4">
                    La Puissance du Stock <span class="text-[#FFC107]">Automobile en Gros</span>
                </h1>
                <p class="text-zinc-400 text-sm sm:text-base mb-6">
                    Accédez à plus de <strong>47 000 références</strong> en direct de notre ERP iDempiere. Tarifs préférentiels pour garages et revendeurs.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ url('/articles') }}" class="bg-[#FFC107] hover:bg-[#FFD54F] text-black font-black px-6 py-3 rounded-xl shadow-[0_4px_20px_rgba(255,193,7,0.35)] transition-all flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-layer-group"></i> Explorer le Catalogue
                    </a>
                    <button class="bg-zinc-900 hover:bg-zinc-800 text-white font-bold border border-zinc-700 px-6 py-3 rounded-xl transition-all text-sm">
                        ⚡ Commande Rapide par Fichier Excel
                    </button>
                </div>
            </div>
        </div>

        {{-- 3 Sample Product Cards with Lion Gold Badges --}}
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i class="fa-solid fa-fire text-[#FFC107]"></i> Pièces les plus commandées cette semaine
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @for ($i = 1; $i <= 3; $i++)
            <div class="bg-[#141824] border border-zinc-800 hover:border-[#FFC107]/60 rounded-2xl p-5 transition-all shadow-xl hover:shadow-[0_0_25px_rgba(255,193,7,0.15)] group">
                <div class="flex justify-between items-center mb-3">
                    <span class="bg-black text-[#FFC107] font-mono font-bold text-xs px-2.5 py-1 rounded-lg border border-[#FFC107]/40">
                        001 AZ201{{ $i }}
                    </span>
                    <span class="text-emerald-400 bg-emerald-950/60 border border-emerald-800/50 text-[11px] font-bold px-2 py-0.5 rounded-full">
                        🟢 120 en stock
                    </span>
                </div>
                <h4 class="font-bold text-white group-hover:text-[#FFC107] transition-colors mb-1">
                    CLEF CONT/CHAUF/DEMAR/LUCAS #{{ $i }}
                </h4>
                <p class="text-xs text-zinc-500 mb-4">Catégorie: Électricité & Allumage</p>
                <div class="flex items-baseline justify-between pt-3 border-t border-zinc-800">
                    <span class="text-xs text-zinc-400">Prix HT</span>
                    <span class="text-xl font-black text-white">18.500 <small class="text-xs text-[#FFC107]">DT</small></span>
                </div>
            </div>
            @endfor
        </div>
    </main>

</body>
</html>