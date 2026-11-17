@extends('layouts.admin')

@section('title', 'Utilisateurs')

@section('content')

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-black text-white">
            <span class="text-[#FFC107]"><i class="fa-solid fa-users mr-2"></i></span>Utilisateurs
        </h1>
        <p class="text-zinc-500 text-sm mt-1">Gestion des utilisateurs</p>
    </div>
</div>

<div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 mb-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-zinc-300">
            <thead class="bg-zinc-800 text-[#FFC107] uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Client associé</th>
                    <th class="px-4 py-3 text-left">Etat</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $index => $u)
                <tr class="border-b border-zinc-800 hover:bg-zinc-800/50 transition-colors" x-data="{ editModalOpen: false }">
                    <td class="px-4 py-3">{{ $index+1 }}</td>
                    <td class="px-4 py-3 font-semibold text-white">{{ $u->name }}</td>
                    <td class="px-4 py-3">{{ $u->email }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 bg-zinc-800 rounded-full text-xs font-bold text-zinc-300">
                            {{ ucfirst($u->role) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($u->code_client)
                            <div class="flex flex-col">
                                <span class="font-mono text-xs font-bold text-[#FFC107]">{{ $u->code_client }}</span>
                                <span class="text-xs text-zinc-400 truncate max-w-[150px]">{{ $u->client->rs ?? '' }}</span>
                            </div>
                        @else
                            <span class="text-zinc-600 text-xs italic">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $u->state == 'Actif' ? 'bg-green-900/60 text-green-300' : 'bg-red-900/60 text-red-300' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $u->state == 'Actif' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                            {{ $u->state }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <button @click="editModalOpen = true" class="text-blue-400 hover:text-blue-300 transition-colors">
                            <i class="fa fa-edit"></i>
                        </button>

                        <!-- Modal Edit -->
                        <div x-show="editModalOpen" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 text-left" style="display: none;" x-transition>
                            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-2xl overflow-hidden" @click.away="editModalOpen = false">
                                <div class="flex justify-between items-center p-6 border-b border-zinc-800">
                                    <h3 class="text-lg font-bold text-white">Modifier un Utilisateur</h3>
                                    <button @click="editModalOpen = false" class="text-zinc-500 hover:text-white transition-colors">
                                        <i class="fa-solid fa-xmark text-xl"></i>
                                    </button>
                                </div>
                                
                                <form action="{{ route('users.update') }}" method="POST">
                                    @csrf
                                    <div class="p-6 space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-zinc-400 mb-1">Nom:</label>
                                                <input type="text" name="name" value="{{ $u->name }}" class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white placeholder-zinc-500 w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none">
                                                @error('name')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-zinc-400 mb-1">Email:</label>
                                                <input type="email" name="email" value="{{ $u->email }}" class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white placeholder-zinc-500 w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none">
                                                @error('email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4 mt-4">
                                            <div>
                                                <label class="block text-sm font-medium text-zinc-400 mb-2">Role:</label>
                                                <div class="space-y-2">
                                                    <label class="flex items-center gap-2">
                                                        <input type="radio" name="role" value="client" {{ $u->role == 'client' ? 'checked' : '' }} class="text-[#FFC107] focus:ring-[#FFC107]"> Client
                                                    </label>
                                                    <label class="flex items-center gap-2">
                                                        <input type="radio" name="role" value="vendeur" {{ $u->role == 'vendeur' ? 'checked' : '' }} class="text-[#FFC107] focus:ring-[#FFC107]"> Vendeur
                                                    </label>
                                                    <label class="flex items-center gap-2">
                                                        <input type="radio" name="role" value="admin" {{ $u->role == 'admin' ? 'checked' : '' }} class="text-[#FFC107] focus:ring-[#FFC107]"> Admin
                                                    </label>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-zinc-400 mb-2">Etat & Code Client:</label>
                                                
                                                <div class="mb-4">
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="checkbox" name="state" value="Actif" {{ $u->state == 'Actif' ? 'checked' : '' }} class="rounded text-[#FFC107] focus:ring-[#FFC107]"> Actif
                                                    </label>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Code Client</label>
                                                    <div class="flex gap-2">
                                                        <input type="text" id="codeInput{{ $u->id }}" name="code" value="{{ $u->code_client }}" class="code-client-input bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none">
                                                        <button type="button" @click.prevent="$dispatch('open-search-modal', { targetInputId: 'codeInput{{ $u->id }}' })" class="bg-zinc-700 hover:bg-zinc-600 text-white px-3 py-2 rounded-xl transition-all" title="Chercher un client">
                                                            <i class="fa-solid fa-search"></i>
                                                        </button>
                                                    </div>
                                                    <p class="text-red-500 text-sm mt-1 code-error"></p>
                                                    <p class="text-green-500 text-sm mt-1 rs-value">{{ $u->client ? $u->client->rs : '' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" value="{{ $u->id }}" name="id_user">
                                    
                                    <div class="p-6 border-t border-zinc-800 flex justify-end gap-3">
                                        <button type="button" @click="editModalOpen = false" class="bg-zinc-700 hover:bg-zinc-600 text-white font-bold px-4 py-2 rounded-xl transition-all">Annuler</button>
                                        <button type="submit" class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all">Modifier</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Global Client Search Modal -->
<div x-data="clientSearch()" @open-search-modal.window="openSearch($event.detail.targetInputId)" x-show="isOpen" class="fixed inset-0 bg-black/60 z-[60] flex items-center justify-center p-4" style="display: none;" x-transition>
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]" @click.away="closeSearch()">
        <div class="flex justify-between items-center p-6 border-b border-zinc-800 shrink-0">
            <h3 class="text-lg font-bold text-white">Rechercher & Associer un Client</h3>
            <button @click="closeSearch()" class="text-zinc-500 hover:text-white transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <div class="p-6 shrink-0 border-b border-zinc-800 bg-zinc-800/20">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500"></i>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="performSearch()" class="bg-zinc-800 border border-zinc-700 rounded-xl pl-12 pr-4 py-3 text-white placeholder-zinc-500 w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none" placeholder="Rechercher par Code, Nom, Raison Sociale...">
            </div>
        </div>
        
        <div class="overflow-y-auto p-0 flex-1">
            <div x-show="isLoading" class="p-8 text-center text-zinc-500">
                <i class="fa-solid fa-spinner fa-spin text-3xl mb-3 text-[#FFC107]"></i>
                <p>Recherche en cours...</p>
            </div>
            
            <div x-show="!isLoading && results.length === 0 && searchQuery.length > 0" class="p-8 text-center text-zinc-500">
                <i class="fa-solid fa-folder-open text-3xl mb-3 opacity-50"></i>
                <p>Aucun client trouvé pour "<span x-text="searchQuery"></span>"</p>
            </div>
            
            <div x-show="!isLoading && results.length > 0" class="divide-y divide-zinc-800">
                <template x-for="client in results" :key="client.code">
                    <div class="p-4 hover:bg-zinc-800/50 flex items-center justify-between transition-colors">
                        <div>
                            <div class="font-bold text-white flex items-center gap-2">
                                <span class="bg-zinc-800 text-[#FFC107] font-mono text-xs px-2 py-0.5 rounded" x-text="client.code"></span>
                                <span x-text="client.rs"></span>
                            </div>
                            <div class="text-sm text-zinc-500 mt-1 flex items-center gap-4">
                                <template x-if="client.email"><span><i class="fa-solid fa-envelope mr-1"></i> <span x-text="client.email"></span></span></template>
                                <template x-if="client.tel"><span><i class="fa-solid fa-phone mr-1"></i> <span x-text="client.tel"></span></span></template>
                            </div>
                        </div>
                        <button type="button" @click="selectClient(client)" class="bg-zinc-800 hover:bg-[#FFC107] text-zinc-300 hover:text-black font-bold px-3 py-1.5 rounded-lg transition-all text-sm flex items-center gap-2 border border-zinc-700 hover:border-[#FFC107]">
                            <i class="fa-solid fa-link"></i> Associer
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="text/javascript">
    // Alpine.js component for Client Search Modal
    document.addEventListener('alpine:init', () => {
        Alpine.data('clientSearch', () => ({
            isOpen: false,
            searchQuery: '',
            results: [],
            isLoading: false,
            targetInputId: null,

            openSearch(targetId) {
                this.targetInputId = targetId;
                this.isOpen = true;
                this.searchQuery = '';
                this.results = [];
                // Focus input after transition
                setTimeout(() => {
                    const input = this.$el.querySelector('input[type="text"]');
                    if (input) input.focus();
                }, 100);
            },

            closeSearch() {
                this.isOpen = false;
            },

            async performSearch() {
                if (this.searchQuery.length < 2) {
                    this.results = [];
                    return;
                }
                
                this.isLoading = true;
                try {
                    const response = await fetch(`{{ route('clients.search.ajax') }}?query=${encodeURIComponent(this.searchQuery)}`);
                    this.results = await response.json();
                } catch (error) {
                    console.error('Search error:', error);
                    this.results = [];
                } finally {
                    this.isLoading = false;
                }
            },

            selectClient(client) {
                if (this.targetInputId) {
                    const targetInput = document.getElementById(this.targetInputId);
                    if (targetInput) {
                        targetInput.value = client.code;
                        const outer = targetInput.closest('div').parentElement;
                        if (outer) {
                            const rsEl = outer.querySelector('.rs-value');
                            const errEl = outer.querySelector('.code-error');
                            if (rsEl) rsEl.textContent = client.rs || '';
                            if (errEl) errEl.textContent = '';
                        }
                        // Trigger input event for the jQuery listener below
                        targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
                this.closeSearch();
            }
        }));
    });

    $(document).ready(function() {
        $(document).on('input', '.code-client-input', function() {
            var code = $(this).val();
            var $wrapper = $(this).closest('div').parent(); // go up to the outer div
            var $error = $wrapper.find('.code-error');
            var $rs = $wrapper.find('.rs-value');

            if (code) {
                $.ajax({
                    url: '{{ route('clients.check') }}',
                    type: 'GET',
                    data: { code_client: code },
                    success: function(response) {
                        if (response.exists) {
                            $rs.text(response.rs);
                            $error.text('');
                        } else {
                            $error.text('Il n\'existe pas un client avec ce code.');
                            $rs.text('');
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            } else {
                $error.text('');
                $rs.text('');
            }
        });
    });
</script>
@endsection
