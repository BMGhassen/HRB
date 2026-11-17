@include('inc.client.header')

<!-- Cart Start -->
<div class="container-fluid pt-5 pb-3">
    <div class="row px-xl-5">
        <div class="col-lg-8 table-responsive mb-5">
            <table class="table table-light table-borderless table-hover text-center mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Num.Commande</th>
                        <th>Total</th>
                        <th>Etat</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    @foreach ($commandes as $c)
                        <tr>
                            <td class="align-middle">{{ $c->id }}</td>
                            <td>{{ $c->getTotal() }} HT</td>
                            <td class="align-middle">
                                <span class="badge-lg px-2
                                    @switch($c->etat)
                                        @case('Validé')
                                            bg-dark text-white
                                            @break
                                        @case('EnCours')
                                            bg-warning text-white
                                            @break
                                        @case('Réservée')
                                            bg-info text-white
                                            @break
                                        @case('Livrée')
                                            bg-success text-white
                                            @break
                                        @default
                                            bg-light
                                    @endswitch">
                                    {{ $c->etat }}
                                </span>
                            </td>
                            <td>
                                <!-- Clickable button or icon -->
                                <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#ligneCommande{{ $c->id }}" aria-expanded="false" aria-controls="ligneCommande{{ $c->id }}">
                                    <i class="fa fa-angle-down text-dark"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Collapsible row -->
                        <tr class="collapse" id="ligneCommande{{ $c->id }}">
                            <td colspan="4"> <!-- Ensure colspan matches the number of table columns (4 in this case) -->
                                <table class="table table-warning">
                                    <thead>
                                        <tr>
                                            <th>Article</th>
                                            <th>Quantité</th>
                                            <th>Prix Unitaire</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-group-divider">
                                        @foreach ($c->ligneCommandes as $lc)
                                            <tr>
                                                <td class="align-middle">
                                                    <img src="img/product-1.jpg" alt="" style="width: 50px;">
                                                    {{ $lc->article->ref }}
                                                </td>
                                                <td class="align-middle">{{ $lc->qte }}</td>
                                                <td class="align-middle">{{ $lc->article->prix }} HT</td>
                                                <td class="align-middle">{{ $lc->article->prix * $lc->qte }} HT</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('inc.client.footer')
