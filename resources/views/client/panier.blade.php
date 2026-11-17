@include('inc.client.header')

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-black text-white">Panier Pro</h2>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="lg:w-2/3">
            
            {{-- Alerts --}}
            @if (session('error'))
                <div class="mb-4 bg-red-900/30 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl"><i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}</div>
            @endif
            @if (session('warning'))
                <div class="mb-4 bg-amber-900/30 border border-amber-500/50 text-amber-400 px-4 py-3 rounded-xl"><i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('warning') }}</div>
            @endif
            @if (session('success'))
                <div class="mb-4 bg-emerald-900/30 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-xl"><i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}</div>
            @endif

            <!-- Quick Add Bar -->
            <div class="bg-lion-card border border-zinc-800 rounded-2xl p-6 mb-8 shadow-xl">
                <h3 class="text-lion-accent font-bold mb-4 flex items-center gap-2"><i class="fa-solid fa-bolt"></i> Ajout Rapide</h3>
                <form action="{{ route('commande.store') }}" method="POST" class="flex flex-col md:flex-row items-stretch gap-4 relative">
                    @csrf
                    <div class="flex-1 relative">
                        <input type="text" id="quickSearchInput" class="w-full bg-zinc-900 border border-zinc-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-[#FFC107] transition-colors" placeholder="Rechercher par Référence ou Désignation..." autocomplete="off">
                        <input type="hidden" name="idarticle" id="quickSearchId" required>
                        <ul id="quickSearchSuggestions" class="absolute w-full mt-2 bg-zinc-900 border border-zinc-700 rounded-xl shadow-2xl z-50 overflow-hidden" style="display:none; max-height: 250px; overflow-y: auto;"></ul>
                    </div>
                    
                    <div class="w-32 flex items-center bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden focus-within:border-[#FFC107] transition-colors">
                        <span class="px-3 text-zinc-400 text-sm font-bold">Qté</span>
                        <input type="number" name="qte" id="quickSearchQty" class="w-full bg-transparent text-white text-center py-3 focus:outline-none" value="1" min="1">
                    </div>
                    
                    <button type="submit" id="quickSearchBtn" class="bg-[#FFC107] hover:bg-[#FFD54F] disabled:opacity-50 text-black font-black px-6 py-3 rounded-xl transition-colors" disabled>
                        Ajouter
                    </button>
                </form>
            </div>
            <!-- End Quick Add Bar -->

            <!-- Cart Table -->
            <div class="bg-lion-card border border-zinc-800 rounded-2xl shadow-xl overflow-x-auto">
                <table class="w-full text-left text-zinc-300">
                    <thead class="bg-zinc-900 border-b border-zinc-800 text-xs uppercase text-zinc-500 font-black">
                        <tr>
                            <th class="px-6 py-4">Article</th>
                            <th class="px-6 py-4 text-right">Prix HT</th>
                            <th class="px-6 py-4 text-center">Quantité</th>
                            <th class="px-6 py-4 text-right">Total HT</th>
                            <th class="px-6 py-4 text-center"><i class="fa-solid fa-trash-can"></i></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @if ($commande && $commande->ligneCommandes->isNotEmpty())
                            @foreach ($commande->ligneCommandes as $lc)
                            <tr class="hover:bg-zinc-900/50 transition-colors">
                                <td class="px-6 py-4 flex items-center gap-4">
                                    <div class="w-12 h-12 bg-[#fff] rounded-lg p-1 flex items-center justify-center overflow-hidden flex-shrink-0">
                                        <img src="{{ asset('build/assets/img/' . $lc->article->ref . '.jpg') }}" onerror="this.src='{{ asset('build/shopassets/img/logo_Header.png') }}'" class="max-w-full max-h-full object-contain mix-blend-multiply">
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $lc->article->ref }}</div>
                                        <div class="text-xs text-zinc-500 truncate max-w-[200px]">{{ $lc->article->designation }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono">{{ $lc->article->prix }} DT</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-1 quantity mx-auto bg-zinc-900 border border-zinc-700 rounded-lg p-1 w-28">
                                        <button type="button" class="btn-minus w-8 h-8 rounded bg-zinc-800 text-zinc-400 hover:text-white hover:bg-zinc-700 transition-colors" data-id="{{$lc->id}}" data-type="minus">
                                            <i class="fa-solid fa-minus text-xs"></i>
                                        </button>
                                        <input type="text" class="qte-input w-8 bg-transparent text-white text-center text-sm font-bold focus:outline-none" value="{{$lc->qte}}" data-id="{{$lc->id}}">
                                        <button type="button" class="btn-plus w-8 h-8 rounded bg-zinc-800 text-zinc-400 hover:text-white hover:bg-zinc-700 transition-colors" data-id="{{$lc->id}}" data-type="plus">
                                            <i class="fa-solid fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-lion-accent">{{ $lc->article->prix * $lc->qte }} DT</td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('lignecommande.destroy', $lc->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button class="w-8 h-8 rounded-lg bg-red-950/50 text-red-400 hover:bg-red-900 hover:text-red-50 border border-red-900/50 transition-colors">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-zinc-500 font-bold">
                                    <i class="fa-solid fa-cart-shopping text-3xl mb-3 block opacity-20"></i>
                                    Votre panier pro est vide
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:w-1/3">
            <div class="bg-lion-card border border-zinc-800 rounded-2xl p-6 sticky top-24 shadow-xl">
                <h3 class="text-xl font-bold text-white mb-6 border-b border-zinc-800 pb-4">Résumé de la Commande</h3>
                
                <div class="flex justify-between items-end mb-8">
                    <span class="text-zinc-400 font-bold">Total HT</span>
                    <span class="text-3xl font-black text-lion-accent total-amount">
                        @if ($commande && $commande->ligneCommandes->isNotEmpty())
                            {{ $commande->getTotal() }} DT
                        @else
                            0 DT
                        @endif
                    </span>
                </div>
                
                @if ($commande)
                <form action="{{ route('commande.valider', $commande->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-[#FFC107] hover:bg-[#FFD54F] text-black font-black py-4 rounded-xl shadow-[0_4px_15px_rgba(255,193,7,0.3)] transition-all transform active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Valider et Envoyer (ERP)
                    </button>
                </form>
                @else
                    <button type="submit" class="w-full bg-zinc-800 text-zinc-500 font-black py-4 rounded-xl cursor-not-allowed" disabled>
                        Panier Vide
                    </button>
                @endif
                
                <p class="text-zinc-500 text-xs mt-4 text-center">
                    <i class="fa-solid fa-shield-halved mr-1"></i> Commande sécurisée et synchronisée avec iDempiere
                </p>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateQuantity(ligneCommandeId, newQuantity) {
        console.log('Updating quantity for LigneCommande ID:', ligneCommandeId, 'New Quantity:', newQuantity);

        // Make an AJAX request to update the quantity on the server
        $.ajax({
            url: '/update-quantity',  // Replace with your route
            method: 'POST',
            data: {
                ligne_commande_id: ligneCommandeId,
                quantity: newQuantity,
                _token: '{{ csrf_token() }}'  // Ensure CSRF token is included
            },
            success: function(response) {
                console.log('Quantity updated successfully');
            },
            error: function(xhr) {
                console.error('Error updating quantity:', xhr);
            }
        });

        // Update the total price for the item
        const input = document.querySelector('input[data-id="' + ligneCommandeId + '"]');
        const price = parseFloat(input.closest('tr').querySelector('td:nth-child(2)').textContent.replace(' DT', '').trim());
        const totalCell = input.closest('tr').querySelector('td:nth-child(4)');
        totalCell.textContent = (price * newQuantity).toFixed(3) + ' DT';

        // Update the overall total
        updateOverallTotal();
    }

    function updateOverallTotal() {
        let total = 0;
        document.querySelectorAll('td:nth-child(4)').forEach(function(cell) {
            total += parseFloat(cell.textContent.replace(' DT', '').trim());
        });
        document.querySelector('.total-amount').textContent = total.toFixed(3) + ' DT';
    }

    // Use event delegation to handle clicks
    document.body.addEventListener('click', function(event) {
        if (event.target.classList.contains('btn-plus') || event.target.classList.contains('btn-minus')) {
            const button = event.target;
            const input = document.querySelector('input[data-id="' + button.dataset.id + '"]');
            if (!input) {
                console.error('Input element not found for LigneCommande ID:', button.dataset.id);
                return;
            }
            let currentQuantity = parseInt(input.value);

            // Increment or decrement based on button type
            if (button.dataset.type === 'plus') {
                currentQuantity += 1;
            } else if (button.dataset.type === 'minus' && currentQuantity > 1) {
                currentQuantity -= 1;
            }

            // Update the input field
            input.value = currentQuantity;

            // Call function to handle the update
            updateQuantity(button.dataset.id, currentQuantity);
        }
    });

    // Add event listener to input fields for direct changes
    document.body.addEventListener('input', function(event) {
        if (event.target.matches('input[data-id]')) {
            const input = event.target;
            const ligneCommandeId = input.dataset.id;
            let newQuantity = parseInt(input.value);

            // Validate the input to ensure it's a valid number
            if (isNaN(newQuantity) || newQuantity <= 0) {
                newQuantity = 1; // Set a default value if input is invalid
                input.value = newQuantity;
            }

            // Call function to handle the update
            updateQuantity(ligneCommandeId, newQuantity);
        }
    });

    // Initial call to update the overall total on page load
    updateOverallTotal();

    // ==========================================
    // Quick Add Search (AJAX Autocomplete)
    // ==========================================
    let searchTimeout = null;
    const searchInput = document.getElementById('quickSearchInput');
    const searchId = document.getElementById('quickSearchId');
    const searchQty = document.getElementById('quickSearchQty');
    const searchBtn = document.getElementById('quickSearchBtn');
    const suggestionsBox = document.getElementById('quickSearchSuggestions');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        searchBtn.disabled = true;
        searchId.value = '';

        if (query.length < 2) {
            suggestionsBox.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('client.article.autocomplete') }}?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    suggestionsBox.innerHTML = '';
                    if (data.length === 0) {
                        suggestionsBox.innerHTML = '<li class="list-group-item text-muted">Aucun article trouvé</li>';
                    } else {
                        data.forEach(item => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item list-group-item-action cursor-pointer d-flex justify-content-between align-items-center';
                            li.style.cursor = 'pointer';
                            
                            // Stock visualization
                            let stockBadge = '';
                            if (item.qte_stock > 10) stockBadge = '<span class="badge bg-success rounded-pill">En Stock</span>';
                            else if (item.qte_stock > 0) stockBadge = `<span class="badge bg-warning text-dark rounded-pill">${item.qte_stock} restants</span>`;
                            else stockBadge = '<span class="badge bg-danger rounded-pill">Épuisé</span>';

                            li.innerHTML = `
                                <div>
                                    <strong>${item.ref}</strong><br>
                                    <small class="text-muted">${item.designation}</small>
                                </div>
                                <div class="text-end">
                                    <span class="d-block text-primary fw-bold">${parseFloat(item.prix).toFixed(3)} DT</span>
                                    ${stockBadge}
                                </div>
                            `;
                            
                            li.addEventListener('click', () => {
                                searchInput.value = item.ref;
                                searchId.value = item.id;
                                suggestionsBox.style.display = 'none';
                                
                                if (item.qte_stock > 0) {
                                    searchBtn.disabled = false;
                                    searchQty.max = item.qte_stock;
                                } else {
                                    searchBtn.disabled = true;
                                }
                            });
                            
                            suggestionsBox.appendChild(li);
                        });
                    }
                    suggestionsBox.style.display = 'block';
                });
        }, 300);
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
});
</script>
<!-- Cart End -->
@include('inc.client.footer')
