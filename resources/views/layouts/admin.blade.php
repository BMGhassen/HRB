<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#0A0D17]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') | Établissement Rekik</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        lion: { light: '#FFD54F', DEFAULT: '#FFC107', dark: '#E5A100', black: '#0A0D17' }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="icon" href="{{ asset('build/assets/img/007.png') }}" type="image/x-icon">
</head>
<body class="h-full bg-[#0A0D17] text-zinc-300 font-sans antialiased overflow-hidden flex" x-data="{ menu: false }" @keydown.escape.window="menu = false">
    <!-- Mobile: dark overlay behind the open menu -->
    <div x-show="menu" x-transition.opacity @click="menu = false" class="fixed inset-0 bg-black/60 z-30 lg:hidden" style="display: none"></div>

    <!-- Sidebar (off-canvas below lg) -->
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-zinc-950 border-r border-zinc-800 flex flex-col flex-shrink-0 transform transition-transform duration-200 lg:static lg:translate-x-0"
           :class="menu ? 'translate-x-0' : '-translate-x-full'">
        <!-- Logo -->
        <div class="h-20 flex items-center justify-center border-b border-zinc-800">
            <a href="{{ url('/admin/dashboard') }}">
                <img src="{{ asset('build/assets/img/007.png') }}" alt="Logo" class="h-16 object-contain">
            </a>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 custom-scrollbar" @click="if ($event.target.closest('a')) menu = false">
            <a href="{{ url('/admin/dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ Request::is('admin/dashboard') ? 'bg-zinc-800 text-[#FFC107] font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50' }}">
                <i class="fa-solid fa-gauge w-5 text-center"></i>
                <span>Tableau de Bord</span>
            </a>
            
            <a href="{{ url('/admin/articles') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ Request::is('admin/articles*') ? 'bg-zinc-800 text-[#FFC107] font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50' }}">
                <i class="fa-solid fa-tags w-5 text-center"></i>
                <span>Articles</span>
            </a>
            
            <a href="{{ url('/admin/users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ Request::is('admin/users*') ? 'bg-zinc-800 text-[#FFC107] font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50' }}">
                <i class="fa-solid fa-users w-5 text-center"></i>
                <span>Utilisateurs</span>
            </a>
            
            <a href="{{ url('/admin/vendeurs') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ Request::is('admin/vendeurs*') ? 'bg-zinc-800 text-[#FFC107] font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50' }}">
                <i class="fa-solid fa-address-card w-5 text-center"></i>
                <span>Vendeurs</span>
            </a>
            
            <a href="{{ url('/admin/clients') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ Request::is('admin/clients*') ? 'bg-zinc-800 text-[#FFC107] font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50' }}">
                <i class="fa-solid fa-user-tie w-5 text-center"></i>
                <span>Clients</span>
            </a>
            
            <a href="{{ route('commandes') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ Request::is('admin/commandes*') ? 'bg-zinc-800 text-[#FFC107] font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50' }}">
                <i class="fa-solid fa-dolly w-5 text-center"></i>
                <span>Commandes</span>
            </a>
            
            <div class="pt-4 mt-4 border-t border-zinc-800">
                <a href="{{ route('erp.status') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ Request::is('admin/erp*') ? 'bg-zinc-800 text-[#FFC107] font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50' }}">
                    <i class="fa-solid fa-rotate w-5 text-center"></i>
                    <span>ERP Sync</span>
                </a>
            </div>
        </nav>
        
        <!-- Footer / Return -->
        <div class="p-4 border-t border-zinc-800">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-zinc-500 hover:text-[#FFC107] text-sm transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Retour au site
            </a>
        </div>
    </aside>

    <!-- Main Content wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Top Navbar -->
        <header class="h-16 bg-zinc-900 border-b border-zinc-800 flex items-center justify-between px-4 lg:px-6 flex-shrink-0 shadow-sm z-10">
            <div class="text-xl font-bold text-white flex items-center gap-3">
                <button type="button" @click="menu = true" class="lg:hidden w-9 h-9 rounded-lg bg-zinc-800 text-zinc-300 hover:text-[#FFC107] flex items-center justify-center" aria-label="Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <span><span class="text-[#FFC107]">Admin</span> Portal</span>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-zinc-800 border border-zinc-700 flex items-center justify-center text-[#FFC107]">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <span class="hidden sm:inline text-sm font-semibold text-white">{{ Auth::user()->name ?? 'Admin' }}</span>
                </div>
                
                <div class="h-6 w-px bg-zinc-700"></div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-zinc-400 hover:text-red-400 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-right-from-bracket"></i> <span class="hidden sm:inline">Déconnexion</span>
                    </button>
                </form>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-4 lg:p-6 scroll-smooth">
            @yield('content')
        </main>
        
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #52525b; }
        
        main::-webkit-scrollbar { width: 8px; height: 8px; }
        main::-webkit-scrollbar-track { background: #0A0D17; }
        main::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
        main::-webkit-scrollbar-thumb:hover { background: #3f3f46; }
    </style>
    
    @yield('scripts')
</body>
</html>
