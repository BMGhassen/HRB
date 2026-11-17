<!DOCTYPE html>
<html lang="fr" class="h-full bg-lion-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inscription – Établissement Rekik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('inc.theme')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('build/shopassets/img/logo_Header.png') }}" type="image/x-icon">
</head>
<body class="min-h-screen bg-lion-black flex items-center justify-center px-4 py-12">
    <div class="fixed top-4 right-4 z-50">@include('inc.theme-toggle', ['class' => 'flex items-center justify-center w-10 h-10 rounded-xl bg-lion-card border border-zinc-700 text-zinc-300 hover:text-lion-accent hover:border-[#FFC107] transition-colors'])</div>

    <div class="w-full max-w-md">

        {{-- Logo --}}
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


        {{-- Card --}}
        <div class="bg-lion-card border border-zinc-800 rounded-3xl p-8 shadow-2xl">
            <h2 class="text-lg font-bold text-white mb-6">Inscription</h2>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-zinc-400 text-sm font-bold mb-2">Nom complet</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                           class="w-full bg-zinc-900 border @error('name') border-red-500 @else border-zinc-700 @enderror text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#FFC107] transition-colors placeholder-zinc-600"
                           placeholder="Nom et Prénom">
                    @error('name')
                        <p class="text-red-400 text-xs mt-2 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-zinc-400 text-sm font-bold mb-2">Adresse Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                           class="w-full bg-zinc-900 border @error('email') border-red-500 @else border-zinc-700 @enderror text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#FFC107] transition-colors placeholder-zinc-600"
                           placeholder="contact@votregarage.tn">
                    @error('email')
                        <p class="text-red-400 text-xs mt-2 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-zinc-400 text-sm font-bold mb-2">Mot de passe</label>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="w-full bg-zinc-900 border @error('password') border-red-500 @else border-zinc-700 @enderror text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#FFC107] transition-colors placeholder-zinc-600"
                           placeholder="••••••••">
                    @error('password')
                        <p class="text-red-400 text-xs mt-2 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-zinc-400 text-sm font-bold mb-2">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full bg-zinc-900 border border-zinc-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#FFC107] transition-colors placeholder-zinc-600"
                           placeholder="••••••••">
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-[#FFC107] to-[#E5A100] text-black font-black py-3.5 rounded-xl shadow-[0_4px_20px_rgba(255,193,7,0.3)] hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2 mt-2">
                    <i class="fa-solid fa-user-plus"></i> Créer mon compte
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-zinc-800 text-center">
                <span class="text-zinc-500 text-sm">Vous avez déjà un compte ?</span>
                <a href="{{ route('login') }}" class="text-lion-accent font-bold text-sm hover:text-lion-accent-light ml-2 transition-colors">Se connecter →</a>
            </div>
        </div>

        <p class="text-center text-zinc-600 text-xs mt-8">
            <a href="{{ url('/') }}" class="hover:text-zinc-400 transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i>Retour à la vitrine
            </a>
        </p>
    </div>

</body>
</html>
