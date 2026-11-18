<!DOCTYPE html>
<html lang="fr" class="h-full bg-lion-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte en attente – Établissement Rekik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('inc.theme')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('build/shopassets/img/logo_Header.png') }}" type="image/x-icon">
</head>
<body class="min-h-screen bg-lion-black flex items-center justify-center px-4 py-12">
    <div class="fixed top-4 right-4 z-50">@include('inc.theme-toggle', ['class' => 'flex items-center justify-center w-10 h-10 rounded-xl bg-lion-card border border-zinc-700 text-zinc-300 hover:text-lion-accent hover:border-[#FFC107] transition-colors'])</div>

    <div class="w-full max-w-lg">
        
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
        <div class="bg-lion-card border border-zinc-800 rounded-3xl p-8 sm:p-10 shadow-2xl text-center">
            
            <div class="w-20 h-20 bg-amber-500/10 border border-amber-500/30 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-clock-rotate-left text-3xl text-amber-500"></i>
            </div>
            
            <h2 class="text-xl sm:text-2xl font-black text-white mb-4">Votre compte est en cours de validation</h2>
            
            <p class="text-zinc-400 text-sm leading-relaxed mb-8">
                Merci de votre inscription. Votre compte a bien été créé, mais il nécessite l'approbation d'un administrateur avant de pouvoir accéder au catalogue et aux tarifs. 
                <br><br>
                Nous vous contacterons très prochainement.
            </p>

            <div class="space-y-4">
                <a href="{{ url('/') }}" class="block w-full bg-zinc-800 hover:bg-zinc-700 text-white font-bold py-3.5 rounded-xl border border-zinc-700 transition-colors">
                    Retour à la vitrine
                </a>
                
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="w-full text-zinc-500 hover:text-white text-sm font-bold transition-colors">
                        Se déconnecter
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-zinc-600 text-xs mt-8">
            Pour toute urgence, contactez-nous au <span class="text-lion-accent">(+216) 71 000 000</span>
        </p>
    </div>

</body>
</html>
