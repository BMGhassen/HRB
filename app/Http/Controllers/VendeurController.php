<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vendeur;
class VendeurController extends Controller
{
    //
    //afficher la liste des articles
    public function index(){
        $vendeurs = Vendeur::all();
        // dd($vendeurs);
        $users = User::all();
        return view('admin.vendeurs.ven')->with('vendeurs',$vendeurs)->with('users', $users);

    }

    //Ajouter un nouvel article
    public function store(Request $request){

        $request->validate([
            'nom' => 'required',
            'email' => 'required',

        ]);

            $vendeur = new Vendeur();
            $vendeur->nom = $request->nom;
            $vendeur->user_id = $request->email;
           // $article->save();



            if($vendeur->save()){
                return redirect()->route('vendeurs')->with('success', 'Vendeur created successfully.');
            }
            else {
                return back()->with('error', 'Failed to create vendeur.');
            }
    }

    public function destroy($id){
        $article = Article::find($id);
        if($article->delete()){
            return redirect()->back();
        }else{
            echo "error";
        }
    }

    public function update(Request $request){
        $request->validate([
            'ref' => 'required',
            'designation' => 'required',
            'stock' => 'required|integer',
            'instance' => 'required|integer',
            'reserve' => 'required|integer',
            'prix' => 'required|numeric',
            'description' => 'required',
        ]);

        $id = $request->id_article;
        $article = Article::find($id);
        $article->ref = $request->ref;
        $article->designation = $request->designation;
        $article->qte_stock = $request->stock;
        $article->qte_instance = $request->instance;
        $article->qte_reserve = $request->reserve;
        $article->prix = $request->prix;
        $article->description = $request->description;
        $article->description = $request->description;
        if($article->update()){
            return redirect()->route('articles')->with('success', 'Article updated successfully.');
        }
        else {
            return back()->with('error', 'Failed to update article.');
        }
    }
}
