@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-black text-white">
        <span class="text-[#FFC107]"><i class="fa-solid fa-gauge mr-2"></i></span>Dashboard
    </h1>
    <p class="text-zinc-500 text-sm mt-1">Outils d'importation et de synchronisation iDempiere ERP & Fichiers</p>
</div>

{{-- ── Section: Synchronisation iDempiere ERP ──────────────────────────────── --}}
<div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4 pb-4 border-b border-zinc-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#FFC107]/10 text-[#FFC107]">
                    <i class="fa-solid fa-cloud-arrow-down text-lg"></i>
                </span>
                <h2 class="text-lg font-bold text-white">Synchronisation ERP iDempiere</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-pulse"></span> Connecté
                </span>
            </div>
            <p class="text-zinc-400 text-xs mt-1">Mise à jour en temps réel des articles, prix et niveaux de stocks depuis l'ERP Cloud.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Fast Stock Sync Button --}}
            <button type="button" id="fastStockBtn" onclick="syncStockFast()"
                    class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 text-sm shadow-lg shadow-emerald-950/40">
                <i class="fa-solid fa-boxes-stacked" id="fastStockIcon"></i>
                <span id="fastStockText">Actualiser Stock</span>
            </button>

            {{-- Full Catalog Sync Button --}}
            <button type="button" id="syncCatalogBtn" onclick="startCatalogSync()"
                    class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 text-sm shadow-lg shadow-amber-950/40">
                <i class="fa-solid fa-rotate" id="syncCatalogIcon"></i>
                <span id="syncCatalogText">Synchroniser Catalogue ERP</span>
            </button>

            {{-- Sync Clients Button --}}
            <form method="POST" action="{{ route('erp.sync.clients') }}" class="inline-block" onsubmit="this.querySelector('button').disabled=true;">
                @csrf
                <button type="submit"
                        class="bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-users-gear text-zinc-400"></i>
                    <span>Sync Clients</span>
                </button>
            </form>

            {{-- Link to full ERP status page --}}
            <a href="{{ route('erp.status') }}"
               class="text-zinc-400 hover:text-[#FFC107] text-sm font-medium px-3 py-2.5 rounded-xl border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-800/40 flex items-center gap-1.5 transition-colors"
               title="Voir la page détaillée ERP">
                <i class="fa-solid fa-chart-line text-xs"></i>
                <span>Statut ERP</span>
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    @php
        $totalArticles = \App\Models\Article::count();
        $erpSynced = \App\Models\Article::whereNotNull('erp_product_id')->count();
        $lastSyncedAt = \App\Models\Article::whereNotNull('erp_synced_at')->max('erp_synced_at');
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
        <div class="bg-zinc-950/60 border border-zinc-800/80 rounded-xl p-3">
            <span class="text-zinc-500 block mb-1">Articles Locaux</span>
            <span class="text-white font-bold text-base">{{ number_format($totalArticles) }}</span>
        </div>
        <div class="bg-zinc-950/60 border border-zinc-800/80 rounded-xl p-3">
            <span class="text-zinc-500 block mb-1">Synchronisés ERP</span>
            <span class="text-[#FFC107] font-bold text-base">{{ number_format($erpSynced) }}</span>
        </div>
        <div class="bg-zinc-950/60 border border-zinc-800/80 rounded-xl p-3">
            <span class="text-zinc-500 block mb-1">Dernière Synchronisation</span>
            <span class="text-zinc-300 font-medium text-xs">
                {{ $lastSyncedAt ? \Carbon\Carbon::parse($lastSyncedAt)->diffForHumans() : '—' }}
            </span>
        </div>
        <div class="bg-zinc-950/60 border border-zinc-800/80 rounded-xl p-3">
            <span class="text-zinc-500 block mb-1">Mode d'import</span>
            <span class="text-emerald-400 font-semibold text-xs flex items-center gap-1">
                <i class="fa-solid fa-bolt text-[10px]"></i> Par lots (100 / req)
            </span>
        </div>
    </div>

    {{-- Progress Bar (Initially hidden) --}}
    <div id="syncProgressContainer" class="hidden mt-4 bg-zinc-950 border border-zinc-800 rounded-xl p-4 transition-all">
        <div class="flex items-center justify-between text-sm mb-2">
            <div class="flex items-center gap-2 overflow-hidden">
                <i class="fa-solid fa-spinner fa-spin text-[#FFC107]" id="progressSpinner"></i>
                <span class="font-bold text-white whitespace-nowrap" id="syncStatusTitle">Synchronisation en cours...</span>
                <span class="text-xs text-zinc-400 truncate" id="syncProgressText">Connexion à iDempiere...</span>
            </div>
            <span class="font-black text-[#FFC107] ml-2" id="syncProgressPercentage">0%</span>
        </div>
        <div class="w-full bg-zinc-800 rounded-full h-3 overflow-hidden">
            <div id="syncProgressBar" class="bg-gradient-to-r from-[#FFC107] to-amber-500 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>
</div>

{{-- Row 1: CSV + Articles + Clients --}}
<div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 mb-6">
    <h2 class="text-sm font-bold text-zinc-400 uppercase tracking-widest mb-4">Importations CSV</h2>
    <div class="flex flex-wrap gap-3">

        {{-- Charger CSV --}}
        <button type="button"
                onclick="document.getElementById('modal-importE').classList.remove('hidden')"
                class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-2">
            <i class="fa fa-file-import"></i> Charger CSV
        </button>

        {{-- MAJ Articles --}}
        <a href="{{ route('imparti') }}"
           class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-2">
            <i class="fa fa-file-import"></i> MAJ Articles
        </a>

        {{-- MAJ Clients --}}
        <a href="{{ route('impcli') }}"
           class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-2">
            <i class="fa fa-file-import"></i> MAJ Clients
        </a>

    </div>
</div>

{{-- Row 2: Equivalents + Origins --}}
<div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 mb-6">
    <h2 class="text-sm font-bold text-zinc-400 uppercase tracking-widest mb-4">Synchronisation Données</h2>
    <div class="flex flex-wrap gap-3">

        <a href="{{ route('equi.import') }}"
           class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-2">
            <i class="fa fa-file-import"></i> MAJ Equivalents
        </a>

        <a href="{{ route('origin.import') }}"
           class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-2">
            <i class="fa fa-file-import"></i> MAJ Origins
        </a>

    </div>
</div>

{{-- Row 3: Image --}}
<div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 mb-6">
    <h2 class="text-sm font-bold text-zinc-400 uppercase tracking-widest mb-4">Médias</h2>
    <div class="flex flex-wrap gap-3">

        <button type="button"
                onclick="document.getElementById('modal-importI').classList.remove('hidden')"
                class="bg-zinc-700 hover:bg-zinc-600 text-white px-4 py-2 rounded-xl flex items-center gap-2">
            <i class="fa fa-regular fa-image"></i> Charger Image
        </button>

    </div>
</div>

{{-- ── Modal: Charger Image ─────────────────────────────────────────────── --}}
<div id="modal-importI"
     class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center">
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">Importer une Image</h3>
            <button onclick="document.getElementById('modal-importI').classList.add('hidden')"
                    class="text-zinc-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form action="{{ route('up.img') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm text-zinc-400 mb-1">Importer un fichier</label>
                <input type="file" id="import_img" name="import_img" required
                       class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white w-full
                              focus:ring-2 focus:ring-[#FFC107] focus:outline-none">
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button"
                        onclick="document.getElementById('modal-importI').classList.add('hidden')"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-xl">
                    Annuler
                </button>
                <button type="submit"
                        class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all">
                    Importer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Charger CSV ───────────────────────────────────────────────── --}}
<div id="modal-importE"
     class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center">
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">Importer un fichier CSV</h3>
            <button onclick="document.getElementById('modal-importE').classList.add('hidden')"
                    class="text-zinc-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form action="{{ route('articles.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm text-zinc-400 mb-1">Importer un fichier</label>
                <input type="file" id="import_csv" name="import_csv" accept=".csv"
                       class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white w-full
                              focus:ring-2 focus:ring-[#FFC107] focus:outline-none">
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button"
                        onclick="document.getElementById('modal-importE').classList.add('hidden')"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-xl">
                    Annuler
                </button>
                <button type="submit"
                        class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all">
                    Importer
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Succès',
        text: '{{ session('success') }}',
        confirmButtonText: 'OK',
        background: '#18181b',
        color: '#fff',
        confirmButtonColor: '#FFC107',
    });
</script>
@endif
@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Erreur',
        text: '{{ session('error') }}',
        confirmButtonText: 'OK',
        background: '#18181b',
        color: '#fff',
        confirmButtonColor: '#ef4444',
    });
</script>
@endif
@if (session('warning'))
<script>
    Swal.fire({
        icon: 'warning',
        title: 'Attention',
        text: '{{ session('warning') }}',
        confirmButtonText: 'OK',
        background: '#18181b',
        color: '#fff',
        confirmButtonColor: '#f59e0b',
    });
</script>
@endif

<script>
    // ── Fast Stock Synchronization ──────────────────────────────────────────
    async function syncStockFast() {
        const btn = document.getElementById('fastStockBtn');
        const icon = document.getElementById('fastStockIcon');
        const text = document.getElementById('fastStockText');

        if (!btn) return;
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        icon.className = 'fa-solid fa-spinner fa-spin';
        text.textContent = 'Mise à jour...';

        try {
            const res = await fetch("{{ url('/admin/erp/sync-stock-now') }}");
            const data = await res.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Stock Actualisé !',
                    text: `${data.articles_updated} articles mis à jour en ${data.duration_ms}ms`,
                    background: '#18181b',
                    color: '#fff',
                    confirmButtonColor: '#FFC107'
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur Stock',
                    text: data.error || 'Erreur lors de la synchronisation du stock.',
                    background: '#18181b',
                    color: '#fff',
                    confirmButtonColor: '#ef4444'
                });
            }
        } catch (e) {
            console.error('Fast Stock Sync Error:', e);
            Swal.fire({
                icon: 'error',
                title: 'Erreur Réseau',
                text: e.message || 'Impossible de joindre le serveur.',
                background: '#18181b',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            });
        } finally {
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            icon.className = 'fa-solid fa-boxes-stacked';
            text.textContent = 'Actualiser Stock';
        }
    }

    // ── Full Chunked Catalog Synchronization ────────────────────────────────
    async function startCatalogSync() {
        const progressContainer = document.getElementById('syncProgressContainer');
        const progressBar       = document.getElementById('syncProgressBar');
        const progressText      = document.getElementById('syncProgressText');
        const progressPercent   = document.getElementById('syncProgressPercentage');
        const statusTitle       = document.getElementById('syncStatusTitle');
        const syncBtn           = document.getElementById('syncCatalogBtn');
        const syncIcon          = document.getElementById('syncCatalogIcon');
        const syncText          = document.getElementById('syncCatalogText');

        if (syncBtn) {
            syncBtn.disabled = true;
            syncBtn.classList.add('opacity-75', 'cursor-not-allowed');
            syncIcon.className = 'fa-solid fa-spinner fa-spin';
            syncText.textContent = 'Synchronisation...';
        }

        // Show progress box
        if (progressContainer) progressContainer.classList.remove('hidden');
        if (progressBar) {
            progressBar.style.width = '0%';
            progressBar.className = 'bg-gradient-to-r from-[#FFC107] to-amber-500 h-3 rounded-full transition-all duration-300';
        }
        if (statusTitle) statusTitle.textContent = 'Synchronisation en cours...';
        if (progressText) progressText.textContent = "Connexion à l'API ERP iDempiere...";
        if (progressPercent) progressPercent.textContent = '0%';

        let page = 1;
        let totalImported = 0;
        let isDone = false;

        while (!isDone) {
            try {
                const response = await fetch("{{ url('/admin/erp/sync-batch') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ page: page, dry_run: false })
                });

                if (!response.ok) {
                    const errData = await response.text();
                    throw new Error(`HTTP ${response.status}: ${errData}`);
                }

                const data = await response.json();

                if (!data.success && data.error) {
                    throw new Error(data.error);
                }

                if (data.done || data.imported_in_batch === 0) {
                    isDone = true;
                    break;
                }

                totalImported += data.imported_in_batch;
                page++;

                // Update Progress percentage
                const estimatedTotal = Math.max(totalImported + 100, 47669);
                const percent = Math.min(98, Math.round((totalImported / estimatedTotal) * 100));

                if (progressBar) progressBar.style.width = percent + '%';
                if (progressPercent) progressPercent.textContent = percent + '%';
                if (progressText) {
                    progressText.innerHTML = `Lot #${page - 1} (${totalImported.toLocaleString()} articles) — <span class="text-[#FFC107]">${data.sample_name || ''}</span>`;
                }

            } catch (error) {
                console.error('Catalog Sync Error:', error);
                if (statusTitle) statusTitle.textContent = 'Échec de la synchronisation';
                if (progressText) progressText.innerHTML = `<span class="text-red-400 font-bold">${error.message}</span>`;
                if (progressBar) progressBar.className = 'bg-red-600 h-3 rounded-full transition-all duration-300';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur Synchronisation',
                    text: error.message,
                    background: '#18181b',
                    color: '#fff',
                    confirmButtonColor: '#ef4444'
                });

                if (syncBtn) {
                    syncBtn.disabled = false;
                    syncBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    syncIcon.className = 'fa-solid fa-rotate';
                    syncText.textContent = 'Synchroniser Catalogue ERP';
                }
                return;
            }
        }

        // Complete!
        if (progressBar) {
            progressBar.style.width = '100%';
            progressBar.className = 'bg-emerald-500 h-3 rounded-full transition-all duration-300';
        }
        if (progressPercent) progressPercent.textContent = '100%';
        if (statusTitle) statusTitle.textContent = 'Synchronisation Terminée !';
        if (progressText) {
            progressText.innerHTML = `🎉 <strong>${totalImported.toLocaleString()} articles</strong> traités avec succès.`;
        }

        Swal.fire({
            icon: 'success',
            title: 'Catalogue Synchronisé !',
            text: `${totalImported.toLocaleString()} articles importés/mis à jour avec succès depuis iDempiere.`,
            background: '#18181b',
            color: '#fff',
            confirmButtonColor: '#FFC107'
        }).then(() => location.reload());
    }
</script>
@endsection
