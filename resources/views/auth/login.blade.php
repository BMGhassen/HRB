<!DOCTYPE html>
<html lang="fr" class="h-full bg-lion-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Espace Client | Établissement Rekik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('build/assets/img/007.png') }}" type="image/x-icon" />

    @include('inc.theme')
</head>
<body class="h-full font-sans antialiased text-white bg-lion-black flex items-center justify-center p-4 selection:bg-[#FFC107] selection:text-black">
    <div class="fixed top-4 right-4 z-50">@include('inc.theme-toggle', ['class' => 'flex items-center justify-center w-10 h-10 rounded-xl bg-lion-card border border-zinc-700 text-zinc-300 hover:text-lion-accent hover:border-[#FFC107] transition-colors'])</div>

    <div class="w-full max-w-md">

        {{-- ── 1. Big Lion Brand Header ──────────────────────────────── --}}
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-block group mb-3">
                <img src="{{ asset('build/assets/img/007.png') }}" 
                     alt="Logo Ets. Habib Rekik" 
                     class="h-20 sm:h-24 w-auto mx-auto object-contain transition-transform duration-300 group-hover:scale-105 filter drop-shadow-[0_0_25px_rgba(255,193,7,0.4)]">
            </a>
            <h1 class="text-white font-black text-2xl sm:text-3xl tracking-tight leading-tight">
                ÉTABLISSEMENT <span class="text-lion-accent">REKIK</span>
            </h1>
            <p class="text-lion-accent-light font-bold text-xs tracking-[0.18em] uppercase mt-1">
                Portail B2B Grossiste & Garages
            </p>
        </div>

        {{-- ── 2. Login Card ─────────────────────────────────────────── --}}
        <div class="bg-lion-card border border-zinc-800 rounded-3xl p-8 shadow-2xl relative overflow-hidden" x-data="{ showPass: false }">
            
            {{-- Subtle Golden Top Accent Line --}}
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-[#FFC107] to-transparent"></div>

            <h2 class="text-lg font-bold text-white mb-1 flex items-center gap-2">
                <i class="fa-solid fa-lock text-lion-accent"></i> Connexion Espace Client
            </h2>
            <p class="text-xs text-zinc-400 mb-6">Accédez à votre compte, tarifs négociés et stock direct ERP.</p>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-950/60 border border-rose-800 text-xs text-rose-300">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email / Code Client --}}
                <div>
                    <label for="email" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-1.5">
                        Email ou Code Client
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-sm"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="nom@garage-exemple.tn"
                               class="w-full bg-zinc-900 border border-zinc-700 rounded-xl px-4 py-3 pl-11 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-[#FFC107] focus:ring-1 focus:ring-[#FFC107] transition-all">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider">
                            Mot de passe
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[11px] text-zinc-400 hover:text-lion-accent transition-colors">
                                Oublié ?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-sm"></i>
                        <input id="password" :type="showPass ? 'text' : 'password'" name="password" required
                               placeholder="••••••••"
                               class="w-full bg-zinc-900 border border-zinc-700 rounded-xl px-4 py-3 pl-11 pr-11 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-[#FFC107] focus:ring-1 focus:ring-[#FFC107] transition-all">
                        <button type="button" @click="showPass = !showPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-zinc-300 text-sm">
                            <i :class="showPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 text-xs text-zinc-400 cursor-pointer select-none hover:text-white">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-zinc-900 border-zinc-700 text-lion-accent focus:ring-[#FFC107] focus:ring-offset-zinc-900">
                        <span>Se souvenir de moi</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3.5 px-4 bg-gradient-to-r from-[#FFC107] to-[#E5A100] text-black font-black rounded-xl text-sm shadow-[0_4px_20px_rgba(255,193,7,0.35)] hover:brightness-110 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <span>Se connecter à l'Espace Client</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>

            {{-- Register Link --}}
            @if (Route::has('register'))
                <div class="mt-6 pt-5 border-t border-zinc-800/80 text-center text-xs text-zinc-400">
                    Vous êtes garagiste ou revendeur ? 
                    <a href="{{ route('register') }}" class="font-bold text-lion-accent hover:underline ml-1">
                        Créer un compte
                    </a>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="text-center mt-6 text-[11px] text-zinc-600">
            &copy; {{ date('Y') }} Établissement Rekik. Tous droits réservés.
        </div>

    </div>

</body>
</html>