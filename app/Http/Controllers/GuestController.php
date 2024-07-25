<?php

namespace App\Http\Controllers;

use App\Models\Origin;
use App\Models\Article;
use App\Models\equivalent;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    //
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

    return view('guest.listA', compact('results', 'searchTerm'));
}

public function autocomplete(Request $request)
    {
        $query = $request->get('query');
        $data = Article::where('ref','LIKE','%' . $query . '%')->get();

        return response()->json($data);
    }

public function productDetails( $id )
    {
        $article = Article::find( $id );
        $equivalent = equivalent::where('prod_id', $id)->get();
        $origines = Origin::where('prod_id', $id)->get();
        return view('guest.details')->with('article',$article)->with('equivalent', $equivalent)->with('origines', $origines);
    }
}
