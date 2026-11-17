<?php

namespace App\Http\Controllers;

use App\Jobs\SendCommandeToErp;
use App\Models\Commande;
use Illuminate\Http\Request;
use App\Models\LigneCommande;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    public function index(){
        $commandes = Commande::where('etat', '!=', 'Brouillon')->get();
        return view('admin.commandes')->with('commandes',$commandes);
    }

    public function validerCommande($commandeId)
    {
        // Find the commande by ID — only if it belongs to the logged-in client
        $commande = Commande::with(['client', 'ligneCommandes.article'])
            ->where('client_id', Auth::id())
            ->find($commandeId);

        if (!$commande || $commande->etat !== 'Brouillon') {
            return redirect()->back()->with('error', 'Impossible de valider la commande.');
        }

        if ($commande->ligneCommandes->isEmpty()) {
            return redirect()->back()->with('error', 'Le panier est vide.');
        }

        // Checks the client can fix right now (a retry would not help)
        if (!$commande->client->code_client) {
            return redirect()->back()->with('error', 'Le client n\'a pas de code ERP (code_client manquant).');
        }

        DB::transaction(function () use ($commande) {
            $commande->etat = 'Validé';
            $commande->erp_status = 'pending';
            $commande->erp_error = null;
            $commande->save();

            // Decrement local stock immediately so the website reflects the order
            foreach ($commande->ligneCommandes as $ligne) {
                $ligne->article?->decrement('qte_stock', $ligne->qte);
            }
        });

        // Send to iDempiere in the background (retries automatically if the VM is unreachable)
        SendCommandeToErp::dispatch($commande->id)->afterCommit();

        return redirect()->back()->with('success', 'Commande validée. Elle est en cours de transfert vers l\'ERP.');
    }

    /**
     * Admin: re-send an order whose ERP transfer failed.
     */
    public function renvoyerErp($commandeId)
    {
        $commande = Commande::findOrFail($commandeId);

        if ($commande->erp_status !== 'failed') {
            return redirect()->back()->with('error', 'Seules les commandes en échec peuvent être renvoyées.');
        }

        $commande->forceFill(['erp_status' => 'pending', 'erp_error' => null])->save();
        SendCommandeToErp::dispatch($commande->id);

        return redirect()->back()->with('success', "Commande #{$commande->id} renvoyée vers l'ERP.");
    }

    public function store(Request $request){
        $commande = Commande::where('client_id', Auth::user()->id)->where('etat','Brouillon')->first();
        if ($commande){
            $existingLc = LigneCommande::where('commande_id', $commande->id)
                                    ->where('article_id', $request->idarticle)
                                    ->first();
            if ($existingLc) {
                $existingLc->qte += $request->qte;
                $existingLc->save();
            } else {
                $lc = new LigneCommande();
                $lc->qte = $request->qte;
                $lc->article_id = $request->idarticle;
                $lc->commande_id = $commande->id;
                $lc->save();
            }
            return redirect()->back()->with('success', 'Produit ajouté au panier avec succès !');
        } else {
            $commande = new Commande();
            $commande->client_id = Auth::user()->id;
            if ($commande->save()){
                $lc = new LigneCommande();
                $lc->qte = $request->qte;
                $lc->article_id = $request->idarticle;
                $lc->commande_id = $commande->id;
                $lc->save();
                return redirect()->back()->with('success', 'Produit ajouté au panier avec succès !');
            } else {
                return redirect()->back()->with('error', 'Impossible de créer la commande');
            }
        }
    }

    public function destroy($id)
    {
        $ligneCommande = $this->ownDraftLine($id);
        if ($ligneCommande) {
            $ligneCommande->delete();
            return redirect('/client/panier')->with('success', 'Ligne de commande supprimée avec succès');
        } else {
            return redirect()->back()->with('error', 'Ligne de commande introuvable');
        }
    }

    public function updateQuantity(Request $request)
    {
        $data = $request->validate([
            'ligne_commande_id' => 'required|integer',
            'quantity'          => 'required|integer|min:1|max:100000',
        ]);

        $lc = $this->ownDraftLine($data['ligne_commande_id']);
        if ($lc) {
            $lc->qte = $data['quantity'];
            $lc->save();
            return response()->json(['message' => 'Quantity updated successfully']);
        }
        return response()->json(['message' => 'LigneCommande not found'], 404);
    }

    /**
     * Find an order line only if it belongs to the logged-in client's draft order.
     */
    private function ownDraftLine($id): ?LigneCommande
    {
        return LigneCommande::where('id', $id)
            ->whereHas('commande', function ($q) {
                $q->where('client_id', Auth::id())->where('etat', 'Brouillon');
            })
            ->first();
    }
}