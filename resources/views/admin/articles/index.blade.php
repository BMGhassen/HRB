@extends('layouts.admin')

@section('title', 'Gestion des Pièces')

@section('content')

{{-- ── Header & Action Buttons ──────────────────────────────────────────── --}}
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-white">
            <span class="inline-flex items-center bg-[#FFC107] text-black text-xs font-black px-2 py-0.5 rounded mr-2">
                <i class="fa fa-bolt mr-1"></i> HR-B2B
            </span>
            Catalogue des Pièces Auto
        </h1>
        <p class="text-zinc-500 text-sm mt-1">Gestion centralisée du stock et synchronisation directe avec iDempiere Cloud ERP</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <button type="button" id="quickStockBtn" onclick="quickStockSync()"
           class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-2">
            <i class="fa fa-boxes-stacked" id="quickStockIcon"></i> <span id="quickStockText">Actualiser Stock</span>
        </button>
        <a href="{{ url('/admin/erp/status') }}"
           class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-2">
            <i class="fa fa-sync-alt"></i> Synchronisation ERP
        </a>
        <button onclick="document.getElementById('modal-addArticle').classList.remove('hidden')"
                class="bg-zinc-700 hover:bg-zinc-600 text-white px-4 py-2 rounded-xl flex items-center gap-2">
            <i class="fa fa-plus text-[#FFC107]"></i> Nouvel Article
        </button>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="bg-green-900/40 border border-green-700 text-green-300 px-4 py-3 rounded-xl mb-4 flex items-center justify-between">
        <span><i class="fa fa-check-circle mr-2"></i><strong>Succès!</strong> {{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-white">&times;</button>
    </div>
@endif

{{-- ── Search & Filter Toolbar ───────────────────────────────────────────── --}}
<div class="bg-zinc-900 border border-zinc-800 border-l-4 border-l-[#FFC107] rounded-2xl p-4 mb-4">
    <form action="{{ route('articles') }}" method="GET" class="flex flex-wrap gap-3 items-center">
        {{-- Search --}}
        <div class="flex-1 min-w-48">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#FFC107]">
                    <i class="fa fa-search text-sm"></i>
                </span>
                <input type="text"
                       class="bg-zinc-800 border border-zinc-700 rounded-xl pl-9 pr-3 py-2 text-white placeholder-zinc-500 w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none text-sm"
                       placeholder="SKU, Référence, Désignation..."
                       name="q" value="{{ request('q') }}">
            </div>
        </div>

        {{-- Category --}}
        <div class="min-w-44">
            <select name="category"
                    class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none text-sm"
                    onchange="this.form.submit()">
                <option value="">Toutes les catégories</option>
                @if(isset($categories))
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        {{-- Sort --}}
        <div class="min-w-52">
            <select name="sort"
                    class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none text-sm"
                    onchange="this.form.submit()">
                <option value="ref_asc"   {{ request('sort') == 'ref_asc'   ? 'selected' : '' }}>Référence SKU (A ➔ Z)</option>
                <option value="name_asc"  {{ request('sort') == 'name_asc'  ? 'selected' : '' }}>Désignation (A ➔ Z)</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                <option value="price_desc"{{ request('sort') == 'price_desc'? 'selected' : '' }}>Prix décroissant</option>
                <option value="stock_desc"{{ request('sort') == 'stock_desc'? 'selected' : '' }}>Stock disponible</option>
            </select>
        </div>

        {{-- In-Stock + Reset --}}
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-sm text-zinc-300 cursor-pointer">
                <input type="checkbox" name="in_stock" value="1"
                       {{ request('in_stock') ? 'checked' : '' }}
                       onchange="this.form.submit()"
                       class="accent-[#FFC107]">
                En Stock
            </label>
            @if(request()->hasAny(['q', 'category', 'sort', 'in_stock']))
                <a href="{{ route('articles') }}" class="text-[#FFC107] hover:text-[#FFD54F] text-xs font-bold">✕ Reset</a>
            @endif
            <button type="submit"
                    class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all text-sm">
                Rechercher
            </button>
        </div>
    </form>
</div>

{{-- ── Articles Table ─────────────────────────────────────────────────────── --}}
<div class="bg-zinc-900 border border-zinc-800 rounded-2xl mb-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-zinc-300">
            <thead class="bg-zinc-800 text-[#FFC107] uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left w-12">#</th>
                    <th class="px-4 py-3 text-left w-40">Référence SKU</th>
                    <th class="px-4 py-3 text-left">Désignation &amp; Description</th>
                    <th class="px-4 py-3 text-left">Catégorie</th>
                    <th class="px-4 py-3 text-right w-32">Prix HT</th>
                    <th class="px-4 py-3 text-center w-52">Stock (Disp / Inst / Res)</th>
                    <th class="px-4 py-3 text-center w-20">Vedette</th>
                    <th class="px-4 py-3 text-right w-24">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($articles as $index => $a)
                    <tr class="border-b border-zinc-800 hover:bg-zinc-800/50 transition-colors">
                        <td class="px-4 py-3 text-zinc-500 font-bold text-xs">{{ $articles->firstItem() + $index }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('build/assets/img/' . $a->ref . '.jpg') }}"
                                     onerror="this.onerror=null; this.src='https://placehold.co/40x40/09090b/fbbf24?text=PART';"
                                     class="rounded border border-[#FFC107]/40 object-cover"
                                     width="36" height="36">
                                <span class="font-mono text-xs font-bold text-[#FFD54F] bg-[#0A0D17] border border-[#FFC107] px-2 py-0.5 rounded">{{ $a->ref }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-white">{{ $a->designation }}</div>
                            @if($a->description && $a->description !== $a->designation)
                                <div class="text-zinc-500 text-xs truncate max-w-xs">{{ $a->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="bg-zinc-800 text-[#FFC107] border border-zinc-700 text-xs px-2 py-0.5 rounded">
                                {{ $a->erp_category ?? 'Standard' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-white">
                            {{ number_format((float)($a->prix ?? 0), 3, '.', ' ') }}
                            <small class="text-[#FFC107] font-bold">DT</small>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-1">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-green-950 text-green-400 border border-green-700" title="Disponible">{{ (int)$a->qte_stock }}</span>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-blue-950 text-blue-400 border border-blue-700" title="En Instance">{{ (int)$a->qte_instance }}</span>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-red-950 text-red-400 border border-red-700" title="Réservé">{{ (int)$a->qte_reserve }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox"
                                   class="update-sel-switch accent-[#FFC107] w-4 h-4 cursor-pointer"
                                   data-id="{{ $a->id }}"
                                   {{ $a->sel == '1' ? 'checked' : '' }}>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <button type="button"
                                        onclick="document.getElementById('modal-editArticle{{ $a->id }}').classList.remove('hidden')"
                                        class="bg-zinc-800 hover:bg-zinc-700 text-[#FFC107] p-1.5 rounded-lg transition-all"
                                        title="Modifier">
                                    <i class="fa fa-edit text-xs"></i>
                                </button>
                                <a href="{{ route('articles.delete', ['id' => $a->id]) }}"
                                   onclick="return confirm('Voulez-vous vraiment supprimer cet article ?')"
                                   class="bg-zinc-800 hover:bg-red-900/60 text-red-400 p-1.5 rounded-lg transition-all"
                                   title="Supprimer">
                                    <i class="fa fa-trash text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-zinc-500">
                            <i class="fa fa-box-open text-4xl block mb-3 text-[#FFC107]"></i>
                            Aucun article trouvé. Cliquez sur <strong>Synchronisation ERP</strong> pour charger les pièces.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Footer --}}
    <div class="bg-zinc-900 border-t border-zinc-800 px-4 py-3 flex flex-col sm:flex-row gap-3 justify-between items-center">
        <div class="text-xs text-zinc-500">
            Affichage de <strong class="text-zinc-300">{{ $articles->firstItem() ?? 0 }}</strong>
            à <strong class="text-zinc-300">{{ $articles->lastItem() ?? 0 }}</strong>
            sur <strong class="text-white">{{ number_format($articles->total(), 0, ',', ' ') }}</strong> pièces auto
        </div>
        {{ $articles->onEachSide(1)->links('vendor.pagination.dark') }}
    </div>
</div>

{{-- ── Edit Article Modals ──────────────────────────────────────────────── --}}
@foreach ($articles as $a)
<div id="modal-editArticle{{ $a->id }}"
     class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center">
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4 border-b border-[#FFC107]/40 pb-4">
            <h3 class="text-base font-bold text-white">
                Modifier l'article :
                <span class="text-[#FFC107] font-mono">{{ $a->ref }}</span>
            </h3>
            <button onclick="document.getElementById('modal-editArticle{{ $a->id }}').classList.add('hidden')"
                    class="text-zinc-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form action="{{ route('articles.update') }}" method="POST">
            @csrf
            <input type="hidden" value="{{ $a->id }}" name="id_article">

            <div class="mb-3">
                <label class="block text-xs font-bold text-[#FFC107] mb-1">Référence (SKU)</label>
                <input type="text"
                       class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white placeholder-zinc-500 w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none"
                       name="ref" value="{{ $a->ref }}" required>
            </div>
            <div class="mb-3">
                <label class="block text-xs font-bold text-[#FFC107] mb-1">Désignation</label>
                <input type="text"
                       class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white placeholder-zinc-500 w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none"
                       name="designation" value="{{ $a->designation }}" required>
            </div>
            <div class="grid grid-cols-3 gap-2 mb-3">
                <div>
                    <label class="block text-xs font-bold text-green-400 mb-1">Stock Disp.</label>
                    <input type="number"
                           class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none"
                           name="stock" value="{{ (int)$a->qte_stock }}" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-blue-400 mb-1">Instance</label>
                    <input type="number"
                           class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none"
                           name="instance" value="{{ (int)$a->qte_instance }}" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-red-400 mb-1">Réservé</label>
                    <input type="number"
                           class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none"
                           name="reserve" value="{{ (int)$a->qte_reserve }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-xs font-bold text-[#FFC107] mb-1">Prix HT (DT)</label>
                <input type="number" step="0.001"
                       class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white font-mono w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none"
                       name="prix" value="{{ $a->prix }}" required>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-bold text-[#FFC107] mb-1">Description</label>
                <textarea class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white placeholder-zinc-500 w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none"
                          name="description" rows="3">{{ $a->description }}</textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-editArticle{{ $a->id }}').classList.add('hidden')"
                        class="bg-zinc-700 hover:bg-zinc-600 text-white px-4 py-2 rounded-xl">
                    Annuler
                </button>
                <button type="submit"
                        class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection

@section('scripts')
<script>
    // AJAX Vedette switch handler
    document.querySelectorAll('.update-sel-switch').forEach(function(sw) {
        sw.addEventListener('change', function() {
            var sw = this;
            var isChecked = sw.checked;
            var articleId = sw.dataset.id;
            sw.disabled = true;

            fetch('{{ route("updateSel") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ id: articleId, sel: isChecked })
            })
            .then(r => r.ok ? r.json() : Promise.reject(new Error('HTTP ' + r.status)))
            .then(res => {
                if (!res.success) throw new Error(res.message || 'échec');
                sw.checked = res.sel === '1';   // reflect what was actually saved
            })
            .catch(e => {
                sw.checked = !isChecked;        // revert: the change was not saved
                console.error('Erreur vedette:', e);
                alert("Le statut vedette n'a pas pu être enregistré.");
            })
            .finally(() => { sw.disabled = false; });
        });
    });

    async function quickStockSync() {
        const btn = document.getElementById('quickStockBtn');
        const icon = document.getElementById('quickStockIcon');
        const text = document.getElementById('quickStockText');

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        icon.className = 'fa fa-spinner fa-spin';
        text.textContent = 'Synchronisation...';

        try {
            const res = await fetch("{{ url('/admin/erp/sync-stock-now') }}");
            const data = await res.json();
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Stock actualisé !',
                        text: `${data.articles_updated} articles mis à jour en ${data.duration_ms}ms`,
                        background: '#18181b',
                        color: '#fff',
                        confirmButtonColor: '#FFC107'
                    }).then(() => location.reload());
                } else {
                    alert(`Stock actualisé : ${data.articles_updated} articles mis à jour.`);
                    location.reload();
                }
            } else {
                alert('Erreur: ' + (data.error || 'Échec de synchronisation'));
            }
        } catch (e) {
            console.error(e);
            alert('Erreur lors de la synchronisation du stock.');
        } finally {
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            icon.className = 'fa fa-boxes-stacked';
            text.textContent = 'Actualiser Stock';
        }
    }
</script>
@endsection