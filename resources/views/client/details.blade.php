@include('inc.client.header')

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-lion-card border border-zinc-800 rounded-3xl p-6 md:p-10 shadow-2xl mb-10">
        <div class="flex flex-col lg:flex-row gap-10">
            
            {{-- Image Section --}}
            <div class="lg:w-2/5 flex items-center justify-center bg-[#fff] rounded-2xl p-6 min-h-[300px] shadow-inner overflow-hidden relative group">
                <img class="max-h-[400px] object-contain mix-blend-multiply group-hover:scale-110 transition-transform duration-500" 
                     src="{{ asset('build/assets/img/' . $article->ref . '.jpg') }}" 
                     onerror="this.src='{{ asset('build/shopassets/img/logo_Header.png') }}'" 
                     alt="{{ $article->ref }}">
                     
                @if($article->qte_stock > 10)
                    <span class="absolute top-4 right-4 text-emerald-400 bg-emerald-950/90 border border-emerald-800/50 text-xs font-bold px-3 py-1 rounded-full shadow-lg">En Stock</span>
                @elseif($article->qte_stock > 0)
                    <span class="absolute top-4 right-4 text-amber-400 bg-amber-950/90 border border-amber-800/50 text-xs font-bold px-3 py-1 rounded-full shadow-lg">Stock Limité</span>
                @else
                    <span class="absolute top-4 right-4 text-red-400 bg-red-950/90 border border-red-800/50 text-xs font-bold px-3 py-1 rounded-full shadow-lg">Rupture de Stock</span>
                @endif
            </div>

            {{-- Details Section --}}
            <div class="lg:w-3/5 flex flex-col justify-center">
                <h1 class="text-3xl md:text-5xl font-black text-white mb-2">{{ $article->ref }}</h1>
                <p class="text-zinc-400 text-lg mb-6 leading-relaxed">{{ $article->designation }}</p>
                
                <div class="mb-8 p-6 bg-zinc-900/50 border border-zinc-800 rounded-2xl">
                    <span class="block text-zinc-500 text-sm font-bold uppercase tracking-wider mb-2">Prix Professionnel</span>
                    <h3 class="text-4xl font-black text-lion-accent">{{ $article->getPrice() }} <span class="text-xl">DT</span> <span class="text-zinc-500 text-sm ml-2 font-normal">HT</span></h3>
                </div>

                <form action="{{route('commande.store')}}" method="POST" class="flex flex-col sm:flex-row gap-4">
                    @csrf
                    <input type="hidden" value="{{$article->id}}" name="idarticle">
                    
                    <div class="flex items-center justify-between bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden px-4 py-2 w-full sm:w-40 focus-within:border-[#FFC107] transition-colors">
                        <button type="button" class="btn-minus text-zinc-400 hover:text-white transition-colors p-2"><i class="fa-solid fa-minus"></i></button>
                        <input type="text" inputmode="numeric" pattern="[0-9]*" class="qte-input w-12 bg-transparent text-white text-center font-bold focus:outline-none" value="1" name="qte">
                        <button type="button" class="btn-plus text-zinc-400 hover:text-white transition-colors p-2"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    
                    <button type="submit" class="flex-1 bg-gradient-to-r from-[#FFC107] to-[#E5A100] hover:brightness-110 text-black font-black px-8 py-4 rounded-xl shadow-[0_4px_15px_rgba(255,193,7,0.3)] transition-all flex items-center justify-center gap-2 transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed" @if($article->qte_stock <= 0) disabled @endif>
                        <i class="fa-solid fa-cart-plus text-lg"></i> Ajouter au Panier Pro
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Tabs Section for References & Equivalents --}}
    <div x-data="{ activeTab: 'origines' }" class="bg-lion-card border border-zinc-800 rounded-3xl p-6 md:p-10 shadow-2xl">
        <div class="flex gap-8 border-b border-zinc-800 mb-8">
            <button @click="activeTab = 'origines'" 
                    :class="activeTab === 'origines' ? 'border-[#FFC107] text-lion-accent' : 'border-transparent text-zinc-500 hover:text-zinc-300'" 
                    class="pb-4 font-bold border-b-2 transition-colors">
                Références Origines
            </button>
            <button @click="activeTab = 'equivalents'" 
                    :class="activeTab === 'equivalents' ? 'border-[#FFC107] text-lion-accent' : 'border-transparent text-zinc-500 hover:text-zinc-300'" 
                    class="pb-4 font-bold border-b-2 transition-colors">
                Équivalents
            </button>
        </div>

        <div class="min-h-[200px]">
            {{-- Origines Tab --}}
            <div x-show="activeTab === 'origines'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                @if($origines->isEmpty())
                    <p class="text-zinc-500 text-center py-10">Aucune référence d'origine trouvée.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($origines as $o)
                        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 flex items-center gap-4 hover:border-[#FFC107]/50 transition-colors cursor-default">
                            <div class="w-10 h-10 rounded-lg bg-zinc-800 flex items-center justify-center text-zinc-400">
                                <i class="fa-solid fa-tag"></i>
                            </div>
                            <div>
                                <h6 class="text-white font-bold">{{ $o->ref_O }}</h6>
                                <p class="text-xs text-zinc-500">{{ $o->marque }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Equivalents Tab --}}
            <div x-show="activeTab === 'equivalents'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                @if($equivalent->isEmpty())
                    <p class="text-zinc-500 text-center py-10">Aucun produit équivalent trouvé.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($equivalent as $e)
                        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 flex items-center gap-4 hover:border-[#FFC107]/50 transition-colors cursor-default">
                            <div class="w-10 h-10 rounded-lg bg-zinc-800 flex items-center justify-center text-zinc-400">
                                <i class="fa-solid fa-code-compare"></i>
                            </div>
                            <div>
                                <h6 class="text-white font-bold">{{ $e->ref }}</h6>
                                <p class="text-xs text-zinc-500">{{ $e->marque }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-minus, .btn-plus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = btn.parentElement.querySelector('.qte-input');
            if (!input) return;
            let val = parseInt(input.value, 10) || 1;
            val = btn.classList.contains('btn-plus') ? val + 1 : Math.max(1, val - 1);
            input.value = val;
        });
    });

    // Keep typed values as positive integers
    document.querySelectorAll('.qte-input').forEach(function (input) {
        input.addEventListener('change', function () {
            input.value = Math.max(1, parseInt(input.value, 10) || 1);
        });
    });
});
</script>

@include('inc.client.footer')
