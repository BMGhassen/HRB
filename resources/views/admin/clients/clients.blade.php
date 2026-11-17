@extends('layouts.admin')

@section('title', 'Clients')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-black text-white">
        <span class="text-[#FFC107]"><i class="fa-solid fa-user-tie mr-2"></i></span>Clients
    </h1>
    <p class="text-zinc-500 text-sm mt-1">Gestion des comptes clients</p>
</div>

{{-- Search Toolbar --}}
<div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 mb-4">
    <form action="{{ route('clients.recherche') }}" method="GET" class="flex gap-3 items-center">
        @csrf
        <div class="relative flex-1 max-w-xs">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#FFC107] text-sm">
                <i class="fa fa-search"></i>
            </span>
            <input type="text"
                   class="bg-zinc-800 border border-zinc-700 rounded-xl pl-9 pr-3 py-2 text-white placeholder-zinc-500 w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none text-sm"
                   placeholder="Rechercher..." name="c">
        </div>
        <button type="submit"
                class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-2">
            <i class="fa fa-search"></i> Rechercher
        </button>
    </form>
</div>

{{-- Clients Table --}}
<div class="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden mb-6">
    <div class="overflow-x-auto" style="max-height:600px; overflow-y:auto;">
        <table class="w-full text-sm text-zinc-300">
            <thead class="bg-zinc-800 text-[#FFC107] uppercase text-xs sticky top-0">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Code</th>
                    <th class="px-4 py-3 text-left">RS</th>
                    <th class="px-4 py-3 text-left">Tel</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clients as $index => $c)
                <tr class="border-b border-zinc-800 hover:bg-zinc-800/50 transition-colors">
                    <td class="px-4 py-3 text-zinc-500 font-bold text-xs">{{ $index+1 }}</td>
                    <td class="px-4 py-3 font-mono text-[#FFC107]">{{ $c->code }}</td>
                    <td class="px-4 py-3 font-medium text-white">{{ $c->rs }}</td>
                    <td class="px-4 py-3 text-zinc-400">{{ $c->tel }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1">
                            <button type="button"
                                    onclick="document.getElementById('modal-editClient{{ $c->user_id }}').classList.remove('hidden')"
                                    class="bg-zinc-800 hover:bg-zinc-700 text-[#FFC107] p-1.5 rounded-lg transition-all"
                                    title="Modifier">
                                <i class="fa fa-edit text-xs"></i>
                            </button>
                            <a href="#" onclick="return confirm('Voulez-vous vraiment supprimer ce client?')"
                               class="bg-zinc-800 hover:bg-red-900/60 text-red-400 p-1.5 rounded-lg transition-all"
                               title="Supprimer">
                                <i class="fa fa-times text-xs"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Edit Modals --}}
@foreach ($clients as $c)
<div id="modal-editClient{{ $c->user_id }}"
     class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center">
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-4 border-b border-[#FFC107]/40 pb-4">
            <h3 class="text-base font-bold text-white">Modifier le client <span class="text-[#FFC107]">{{ $c->rs }}</span></h3>
            <button onclick="document.getElementById('modal-editClient{{ $c->user_id }}').classList.add('hidden')"
                    class="text-zinc-400 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <p class="text-zinc-500 text-sm">Fonctionnalité d'édition à implémenter.</p>
        <div class="flex justify-end mt-4">
            <button type="button"
                    onclick="document.getElementById('modal-editClient{{ $c->user_id }}').classList.add('hidden')"
                    class="bg-zinc-700 hover:bg-zinc-600 text-white px-4 py-2 rounded-xl">
                Fermer
            </button>
        </div>
    </div>
</div>
@endforeach

@endsection
