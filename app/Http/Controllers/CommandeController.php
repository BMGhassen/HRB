<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommandeController extends Controller
{
    //
    public function store(Request $request){
        // dd($request);
        //creation commande
        $commande = new Commande();
        $commande->client_id = Auth::user()->id;
        $commande->save();

        //creation ligne commande
        $lc = new LigneCommande();
        $lc->qte = $request->qte;
        $lc->idarticle = $request->idarticle;
        $lc->qte = $request->qte;

    }
}
