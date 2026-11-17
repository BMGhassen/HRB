@extends('layouts.admin')

@section('title', 'Vendeurs')

@section('content')

<div class="mb-6 flex justify-between items-center" x-data="{ addModalOpen: false }">
    <div>
        <h1 class="text-2xl font-black text-white">
            <span class="text-[#FFC107]"><i class="fa-solid fa-address-card mr-2"></i></span>Vendeurs
        </h1>
        <p class="text-zinc-500 text-sm mt-1">Gestion des vendeurs</p>
    </div>
    
    <button @click="addModalOpen = true" class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Ajouter Vendeur
    </button>

    <!-- Modal Ajout -->
    <div x-show="addModalOpen" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display: none;" x-transition>
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl w-full max-w-md overflow-hidden" @click.away="addModalOpen = false">
            <div class="flex justify-between items-center p-6 border-b border-zinc-800">
                <h3 class="text-lg font-bold text-white">Ajouter un vendeur</h3>
                <button @click="addModalOpen = false" class="text-zinc-500 hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <form action="{{ route('vendeurs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label for="nom" class="block text-sm font-medium text-zinc-400 mb-1">Nom:</label>
                        <input type="text" id="nom" name="nom" class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white placeholder-zinc-500 w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none" placeholder="Enter Input">
                        @error('nom')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-400 mb-1">Email:</label>
                        <select name="email" id="email" class="bg-zinc-800 border border-zinc-700 rounded-xl px-3 py-2 text-white w-full focus:ring-2 focus:ring-[#FFC107] focus:outline-none">
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->email }}</option>
                            @endforeach
                        </select>
                        @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="p-6 border-t border-zinc-800 flex justify-end gap-3">
                    <button type="button" @click="addModalOpen = false" class="bg-zinc-700 hover:bg-zinc-600 text-white font-bold px-4 py-2 rounded-xl transition-all">Annuler</button>
                    <button type="submit" class="bg-[#FFC107] hover:bg-[#E5A100] text-black font-bold px-4 py-2 rounded-xl transition-all">Soumettre</button>
                </div>
            </form>
        </div>
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
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vendeurs as $index => $v)
                <tr class="border-b border-zinc-800 hover:bg-zinc-800/50 transition-colors">
                    <td class="px-4 py-3">{{ $index+1 }}</td>
                    <td class="px-4 py-3 font-semibold text-white">{{ $v->user->name }}</td>
                    <td class="px-4 py-3">{{ $v->user->email }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="#" class="text-blue-400 hover:text-blue-300 transition-colors">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a onclick="return confirm('Voulez-vous vraiment supprimer cet article?')" href="#" class="text-red-400 hover:text-red-300 transition-colors">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
