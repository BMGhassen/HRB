<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Origin;
use App\Models\Article;
use App\Models\equivalent;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function dashboard(){
       $articles = Article::all();
       $produits = Article::where('sel', '1')->get();
        return view('client.dashboard')->with('produits',$produits)->with('articles',$articles);
    }

    public function productDetails( $id )
    {
        $article = Article::find( $id );
        $equivalent = equivalent::where('prod_id', $id)->get();
        $origines = Origin::where('prod_id', $id)->get();
        return view('client.details')->with('article',$article)->with('equivalent', $equivalent)->with('origines', $origines);
    }
    public function index(){
        $clients = Client::all();
        // dd($clients);
        $users = User::all();
        return view('admin.clients.clients')->with('clients',$clients)->with('users', $users);

    }

    //Ajouter un nouvel article
    public function store(Request $request){

        $request->validate([
            'nom' => 'required',
            'email' => 'required',

        ]);

            $Client = new Client();
            $Client->nom = $request->nom;
            $Client->user_id = $request->email;
           // $article->save();



            if($Client->save()){
                return redirect()->route('clients')->with('success', 'Client created successfully.');
            }
            else {
                return back()->with('error', 'Failed to create Client.');
            }
    }

    // public function search(Request $request){
    //         // Validate the search input
    //     $request->validate([
    //         'search' => 'required|string|max:255',
    //     ]);
    //     // dd('$results');
    //     // Perform the search using the search term
    //     $searchTerm = $request->input('search');
    //     if ($request)
    //     {
    //      $key = trim($request->get('q'));
    //      $query = Article::where('ref', 'LIKE', "%{$searchTerm}%")->orderBy('ref', 'ASC');

    //      $results = $query->paginate(20)->withQueryString();


    //     }
    //     else
    //     {
    //      $results = Article::orderBy('ref','ASC');
    //     }
    //     // Return the search results view with the results
    //     return view('client.listA', compact('results', 'searchTerm'));
    // }
    public function search(Request $request)
{
    // Validate the search input
    $request->validate([
        'search' => 'required|string|max:255',
    ]);

    // Perform the search using the search term
    $searchTerm = $request->input('search');

    $query = Article::where('ref', 'LIKE', "%{$searchTerm}%")->orderBy('ref', 'ASC');

    $results = $query->paginate(20)->withQueryString();

    return view('client.listA', compact('results', 'searchTerm'));
}

    // public function showSearch()
    // {
    //     return view('client.listA');
    // }

    public function panier(){
        return view('client.panier');
    }


    // public function update(Request $request){
    //     $request->validate([
    //         'ref' => 'required',
    //         'designation' => 'required',
    //         'stock' => 'required|integer',
    //         'instance' => 'required|integer',
    //         'reserve' => 'required|integer',
    //         'prix' => 'required|numeric',
    //         'description' => 'required',
    //     ]);

    //     $id = $request->id_article;
    //     $article = Article::find($id);
    //     $article->ref = $request->ref;
    //     $article->designation = $request->designation;
    //     $article->qte_stock = $request->stock;
    //     $article->qte_instance = $request->instance;
    //     $article->qte_reserve = $request->reserve;
    //     $article->prix = $request->prix;
    //     $article->description = $request->description;
    //     $article->description = $request->description;
    //     if($article->update()){
    //         return redirect()->route('articles')->with('success', 'Article updated successfully.');
    //     }
    //     else {
    //         return back()->with('error', 'Failed to update article.');
    //     }
    // }
}
