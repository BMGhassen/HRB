@extends('layouts.admin')

@section('title', 'Commandes')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-black text-white">
        <span class="text-[#FFC107]"><i class="fa-solid fa-dolly mr-2"></i></span>Commandes
    </h1>
    <p class="text-zinc-500 text-sm mt-1">Liste de toutes les commandes clients</p>
</div>

<div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 mb-6 overflow-x-auto">
    <table class="w-full text-sm text-zinc-300">
        <thead class="bg-zinc-800 text-[#FFC107] uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">#</th>
                <th class="px-4 py-3 text-left">Num. Commande</th>
                <th class="px-4 py-3 text-left">Etat</th>
                <th class="px-4 py-3 text-left">ERP</th>
                <th class="px-4 py-3 text-left">Client</th>
                <th class="px-4 py-3 text-left">Total</th>
                <th class="px-4 py-3 text-left">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ( $commandes as $index => $co )
            {{-- Main row: clickable to toggle detail --}}
            <tr class="border-b border-zinc-800 hover:bg-zinc-800/50 transition-colors cursor-pointer"
                onclick="toggleDetail('detail-{{ $co->id }}')">
                <td class="px-4 py-3">{{ $index+1 }}</td>
                <td class="px-4 py-3 font-mono text-[#FFC107]">{{ $co->id }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold
                        @switch($co->etat)
                            @case('Validé') bg-zinc-800 text-white @break
                            @case('EnCours') bg-yellow-900/60 text-yellow-300 @break
                            @case('Réservée') bg-blue-900/60 text-blue-300 @break
                            @case('Livrée') bg-green-900/60 text-green-300 @break
                            @default bg-zinc-700 text-zinc-300
                        @endswitch">
                        <span class="w-1.5 h-1.5 rounded-full
                            @switch($co->etat)
                                @case('Validé') bg-white @break
                                @case('EnCours') bg-yellow-400 @break
                                @case('Réservée') bg-blue-400 @break
                                @case('Livrée') bg-green-400 @break
                                @default bg-zinc-400
                            @endswitch"></span>
                        {{ $co->etat }}
                    </span>
                </td>
                <td class="px-4 py-3" onclick="event.stopPropagation()">
                    @switch($co->erp_status)
                        @case('sent')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-900/60 text-green-300"
                                  title="Envoyée {{ optional($co->erp_synced_at)->format('d/m/Y H:i') }}">
                                <i class="fa-solid fa-check mr-1"></i>Envoyée #{{ $co->erp_order_id }}
                            </span>
                            @break
                        @case('pending')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-900/60 text-yellow-300"
                                  title="Tentatives : {{ $co->erp_attempts }}">
                                <i class="fa-solid fa-clock mr-1"></i>En attente
                            </span>
                            @break
                        @case('failed')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-900/60 text-red-300"
                                  title="{{ $co->erp_error }}">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>Échec
                            </span>
                            <form method="POST" action="{{ route('commandes.renvoyer', $co->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="ml-2 text-xs text-[#FFC107] hover:underline">Renvoyer</button>
                            </form>
                            @break
                        @default
                            <span class="text-zinc-600 text-xs">—</span>
                    @endswitch
                </td>
                <td class="px-4 py-3">{{ $co->client->name }}</td>
                <td class="px-4 py-3 font-mono font-bold">{{ $co->getTotal() }}</td>
                <td class="px-4 py-3">
                    <i class="fa-solid fa-chevron-down text-zinc-500 text-xs transition-transform" id="chevron-{{ $co->id }}"></i>
                </td>
            </tr>

            {{-- Collapsible detail row --}}
            <tr id="detail-{{ $co->id }}" class="hidden">
                <td colspan="7" class="p-0 bg-zinc-950">
                    <div class="p-4">
                        <table class="w-full text-xs text-zinc-400">
                            <thead class="bg-zinc-800 text-[#FFC107] uppercase">
                                <tr>
                                    <th class="px-3 py-2 text-left">Article</th>
                                    <th class="px-3 py-2 text-left">Quantité</th>
                                    <th class="px-3 py-2 text-left">Prix Unitaire</th>
                                    <th class="px-3 py-2 text-left">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($co->ligneCommandes as $lc)
                                <tr class="border-b border-zinc-800/50 hover:bg-zinc-800/40">
                                    <td class="px-3 py-2 font-mono">{{ $lc->article->ref }}</td>
                                    <td class="px-3 py-2">{{ $lc->qte }}</td>
                                    <td class="px-3 py-2">{{ $lc->article->prix }} HT</td>
                                    <td class="px-3 py-2 font-bold text-[#FFC107]">{{ $lc->article->prix * $lc->qte }} HT</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@section('scripts')
<script>
    function toggleDetail(id) {
        const row = document.getElementById(id);
        const commandeId = id.replace('detail-', '');
        const chevron = document.getElementById('chevron-' + commandeId);
        if (row) {
            row.classList.toggle('hidden');
            if (chevron) chevron.style.transform = row.classList.contains('hidden') ? '' : 'rotate(180deg)';
        }
    }
</script>
@endsection
