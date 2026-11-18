@extends('layouts.admin')

@section('title', 'iDempiere ERP Sync')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Page Header ───────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <span style="color:#5B5EA6;">⚡</span> iDempiere ERP Sync
            </h1>
            <p class="text-muted mb-0">Products are synced from iDempiere into the local articles catalog.</p>
        </div>
        <div class="d-flex gap-2">
            {{-- Fast Stock Sync Button --}}
            <button type="button" id="fastStockBtn" class="btn btn-success text-white">
                📦 Sync Stock (Fast)
            </button>

            {{-- Full Catalog Sync Button --}}
            <button type="button" id="syncCatalogBtn" class="btn btn-primary">
                🔄 Sync Catalog
            </button>

            {{-- Dry-run Button --}}
            <button type="button" id="dryRunBtn" class="btn btn-outline-secondary">
                👁 Dry Run
            </button>

            {{-- Sync Clients Button --}}
            <form method="POST" action="{{ route('erp.sync.clients') }}" id="syncClientsForm">
                @csrf
                <button type="submit" class="btn btn-warning fw-bold" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Syncing…'; this.form.submit();">
                    👥 Sync Clients
                </button>
            </form>
        </div>
    </div>

    {{-- ── Real-Time Sync Progress Bar ────────────────────────────────────────── --}}
    <div id="syncProgressContainer" class="card border-0 shadow-sm mb-4" style="display: none;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end mb-2">
                <div>
                    <h5 class="mb-0 fw-bold text-primary">Sync in Progress...</h5>
                    <small class="text-muted" id="syncProgressText">Preparing sync...</small>
                </div>
                <div class="fw-bold fs-4 text-primary" id="syncProgressPercentage">0%</div>
            </div>
            <div class="progress" style="height: 12px;">
                <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
    </div>

    {{-- ── Stat Cards ─────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-2">{{ $apiPing['connected'] ? '🟢' : '🔴' }}</div>
                        <div>
                            <div class="text-muted small">API Status</div>
                            <div class="fw-semibold">
                                {{ $apiPing['connected'] ? 'Connected' : 'Disconnected' }}
                            </div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $apiPing['base_url'] ?? '' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Local Articles</div>
                    <div class="fw-bold fs-3">{{ number_format($totalArticles) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Synced from ERP</div>
                    <div class="fw-bold fs-3 text-primary">{{ number_format($erpSynced) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Last Sync</div>
                    <div class="fw-semibold">
                        {{ $lastSyncedAt ? \Carbon\Carbon::parse($lastSyncedAt)->diffForHumans() : '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Client Sync Stats ────────────────────────────────────────────────── --}}
    <h5 class="fw-bold mb-3 mt-2">👥 Client Sync (C_BPartner)</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Local Clients</div>
                    <div class="fw-bold fs-3">{{ number_format($totalClients) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Synced from ERP</div>
                    <div class="fw-bold fs-3 text-warning">{{ number_format($clientsErpSynced) }}</div>
                    <div class="text-muted" style="font-size:.75rem;">
                        {{ $totalClients > 0 ? round(($clientsErpSynced / $totalClients) * 100) : 0 }}% synced
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Last Client Sync</div>
                    <div class="fw-semibold">
                        {{ $lastClientSync ? \Carbon\Carbon::parse($lastClientSync)->diffForHumans() : '—' }}
                    </div>
                    @if($lastClientSync)
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ \Carbon\Carbon::parse($lastClientSync)->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ❌ {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            ⚠️ {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Browse ERP Products ─────────────────────────────────────────────── --}}
    <h5 class="fw-bold mb-3 mt-2">📦 Product Catalog (M_Product)</h5>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-semibold d-flex justify-content-between align-items-center">
            <span>Browse ERP Products (live from iDempiere API)</span>
            <button class="btn btn-sm btn-outline-secondary" id="loadErpBtn">Load Products</button>
        </div>
        <div class="card-body p-0">
            <div id="erpProductsContainer" class="p-3 text-muted small">
                Click "Load Products" to fetch the current product list directly from iDempiere.
            </div>
        </div>
    </div>

</div>

{{-- ── JAVASCRIPT (Global Window Bindings) ───────────────────────────────── --}}
<script>
// 1. Attach functions globally to window object
window.syncStockOnlyFast = async function () {
    const btn = document.getElementById('fastStockBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating…';
    }

    try {
        const res = await fetch("{{ url('/admin/erp/sync-stock-now') }}", { headers: { 'Accept': 'application/json' } });
        const data = (res.headers.get('content-type') || '').includes('application/json')
            ? await res.json()
            : { success: false, error: `Erreur serveur (${res.status}). Voir storage/logs/laravel.log.` };
        
        if (data.success) {
            alert(`🎉 Stock Updated Successfully! (${data.articles_updated} items updated in ${data.duration_ms}ms)`);
            window.location.reload();
        } else {
            alert('Stock Sync Error: ' + (data.error || 'Unknown error'));
        }
    } catch (e) {
        alert('Network Error: ' + e.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '📦 Sync Stock (Fast)';
        }
    }
};

window.startChunkedSync = async function (isDryRun = false) {
    const progressContainer = document.getElementById('syncProgressContainer');
    const progressBar = document.getElementById('syncProgressBar');
    const progressText = document.getElementById('syncProgressText');
    const progressPercentage = document.getElementById('syncProgressPercentage');
    const syncBtn = document.getElementById('syncCatalogBtn');
    const dryBtn = document.getElementById('dryRunBtn');

    if (syncBtn) {
        syncBtn.disabled = true;
        syncBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Syncing…';
    }
    if (dryBtn) dryBtn.disabled = true;

    // Reveal Progress Bar
    if (progressContainer) progressContainer.style.display = 'block';
    if (progressBar) {
        progressBar.style.width = '0%';
        progressBar.classList.add('progress-bar-animated');
        progressBar.classList.remove('bg-success', 'bg-danger');
    }
    if (progressText) progressText.innerText = 'Connecting to iDempiere REST API...';
    if (progressPercentage) progressPercentage.innerText = '0%';

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
                body: JSON.stringify({ page: page, dry_run: isDryRun })
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

            // Update Progress Bar
            let estimatedTotal = Math.max(totalImported + 100, 47669);
            let percent = Math.min(98, Math.round((totalImported / estimatedTotal) * 100));

            if (progressBar) progressBar.style.width = percent + '%';
            if (progressPercentage) progressPercentage.innerText = percent + '%';
            if (progressText) {
                progressText.innerHTML = `Importing Batch #${page - 1} (<strong>${totalImported}</strong> items) — <span class="text-primary">${data.sample_name || ''}</span>`;
            }

        } catch (error) {
            console.error('Sync Error:', error);
            if (progressText) progressText.innerHTML = `<span class="text-danger fw-bold">Error: ${error.message}</span>`;
            if (progressBar) {
                progressBar.classList.add('bg-danger');
                progressBar.classList.remove('progress-bar-animated');
            }
            if (syncBtn) {
                syncBtn.disabled = false;
                syncBtn.innerHTML = '🔄 Sync Catalog';
            }
            if (dryBtn) dryBtn.disabled = false;
            return;
        }
    }

    // 100% Finished!
    if (progressBar) {
        progressBar.style.width = '100%';
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-success');
    }
    if (progressPercentage) progressPercentage.innerText = '100%';
    if (progressText) {
        progressText.innerHTML = `🎉 <strong>Sync Completed!</strong> Processed <strong>${totalImported}</strong> products. Reloading...`;
    }

    setTimeout(() => {
        window.location.reload();
    }, 1500);
};

// 2. Attach Event Listeners on DOM Load
document.addEventListener('DOMContentLoaded', function () {
    const syncCatalogBtn = document.getElementById('syncCatalogBtn');
    const fastStockBtn = document.getElementById('fastStockBtn');
    const dryRunBtn = document.getElementById('dryRunBtn');
    const loadErpBtn = document.getElementById('loadErpBtn');

    if (syncCatalogBtn) {
        syncCatalogBtn.addEventListener('click', function () {
            window.startChunkedSync(false);
        });
    }

    if (dryRunBtn) {
        dryRunBtn.addEventListener('click', function () {
            window.startChunkedSync(true);
        });
    }

    if (fastStockBtn) {
        fastStockBtn.addEventListener('click', function () {
            window.syncStockOnlyFast();
        });
    }

    if (loadErpBtn) {
        loadErpBtn.addEventListener('click', function () {
            const container = document.getElementById('erpProductsContainer');
            container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><div class="mt-2 text-muted">Fetching live from iDempiere…</div></div>';

            fetch('{{ url("/admin/erp/products") }}')
                .then(r => r.json())
                .then(data => {
                    if (data.status !== 'ok') {
                        container.innerHTML = `<div class="alert alert-danger m-3"><strong>API Error:</strong> ${data.error}</div>`;
                        return;
                    }
                    if (!data.records || data.records.length === 0) {
                        container.innerHTML = '<div class="p-3 text-muted">No products returned from iDempiere.</div>';
                        return;
                    }

                    let html = `<div class="p-3 text-muted small mb-2">Showing ${data.records.length} records.</div>`;
                    html += '<div class="table-responsive"><table class="table table-sm table-hover mb-0">';
                    html += '<thead class="table-light"><tr><th>ERP ID</th><th>SKU (Value)</th><th>Name</th></tr></thead><tbody>';

                    data.records.forEach(p => {
                        const id   = p.id ?? p.m_product_id ?? '—';
                        const val  = p.value ?? '—';
                        const name = p.name ?? '—';
                        html += `<tr><td>${id}</td><td><code>${val}</code></td><td>${name}</td></tr>`;
                    });

                    html += '</tbody></table></div>';
                    container.innerHTML = html;
                });
        });
    }
});
</script>
@endsection