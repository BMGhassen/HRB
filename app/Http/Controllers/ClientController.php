<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Client;
use App\Models\Origin;
use App\Models\Article;
use App\Models\Commande;
use Illuminate\View\View;
use App\Jobs\ImportClient;
use App\Models\equivalent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\ConsoleOutput;

class ClientController extends Controller
{
    public function dashboard(){
        // Only the featured products: the full catalog (~48k ERP articles) does not fit in memory
        $produits = Article::where('sel', '1')->get();
        return view('client.dashboard')->with('produits', $produits);
    }

    public function pending()
    {
        return view('client.pending');
    }

    public function commandes(){
        $commandes = Commande::where('client_id', Auth::user()->id)->where('etat', '!=', 'Brouillon')->get();
        return view('client.commandes')->with('commandes',$commandes);
    }

    public function productDetails( $id )
    {
        $article = Article::find( $id );
        $equivalent = equivalent::where('prod_id', $id)->get();
        $origines = Origin::where('prod_id', $id)->get();
        return view('client.details')->with('article',$article)->with('equivalent', $equivalent)->with('origines', $origines);
    }
    public function autocomplete(Request $request)
    {
        $query = $request->get('query');
        if (!$query) return response()->json([]);

        $data = Article::where('ref', 'LIKE', '%' . $query . '%')
            ->orWhere('designation', 'LIKE', '%' . $query . '%')
            ->select('id', 'ref', 'designation', 'qte_stock', 'prix')
            ->take(15)
            ->get();

        return response()->json($data);
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


    public function search(Request $request)
{
    // Validate the search input
    $request->validate([
        'search' => 'required|string|max:255',
    ]);

    // Perform the search using the search term
    $searchTerm = $request->input('search');

    $query = Article::where('ref', 'LIKE', "%{$searchTerm}%")->orderBy('ref', 'ASC');
    $articles2 = article::whereIn('id', equivalent::select('prod_id')->where('ref','like', "%{$searchTerm}%" ));
    $articles3 = article::whereIn('id', Origin::select('prod_id')->where('ref_O','like', "%{$searchTerm}%" ));
    $results = $query->union($articles2)->union($articles3)->paginate(20)->withQueryString();
    return view('client.listA', compact('results', 'searchTerm'));
}



    public function panier(){
        $commande = Commande::where('client_id', Auth::user()->id)->where('etat','brouillon')->first();
        return view('client.panier')->with('commande', $commande);
    }




    public function importcsv()
    {
          $filePath = storage_path("csvFiles/Clients.CSV");



        if (!file_exists($filePath)) {

            return redirect()->route('clients')->with('error', 'Fichier CSV non trouvé.');
        }
        // Dispatch le job

       ImportClient::dispatch($filePath);


        // Log pour vérification
         //dd($filePath);
        return back()->with('success', 'Importation lancée en arrière-plan.');
    }



    public function recherche(Request $request){
        // $articles = Article::all();
        if ($request)
        {
         $key = trim($request->get('c'));

         //  $articles = article::query()
           $clients = Client::where('code', 'like', "%{$key}%");

        }
        else
        {
         $clients = Client::orderBy('code','ASC');
        }

         return view('admin.clients.clients')->with('clients',$clients ->paginate(20)->withQueryString());
     }

     public function checkCode(Request $request)
     {
         $code = $request->input('code_client') ?? $request->input('code');

         if (!$code) {
             return response()->json(['exists' => false]);
         }

         $client = DB::table('clients')->where('code', $code)->first();

         if ($client) {
             return response()->json(['exists' => true, 'rs' => $client->rs]);
         } else {
             return response()->json(['exists' => false]);
         }
     }
     
     public function searchAjax(Request $request)
     {
         $query = $request->get('query');
         if (!$query) {
             return response()->json([]);
         }

         $clients = Client::where('code', 'LIKE', '%' . $query . '%')
             ->orWhere('rs', 'LIKE', '%' . $query . '%')
             ->select('code', 'rs', 'tel', 'email')
             ->take(20)
             ->get();

         return response()->json($clients);
     }

}
