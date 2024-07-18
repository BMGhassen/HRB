<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    //afficher la liste des articles
    public function index(){

       $articles = Article::orderBy('ref','ASC')->paginate(20);
        return view('admin.articles.index')->with('articles',$articles);
    }


    //Ajouter un nouvel article
    public function store(Request $request){

        $request->validate([
            'ref' => 'required',
            'designation' => 'required',
            'stock' => 'required|integer',
            'instance' => 'required|integer',
            'reserve' => 'required|integer',
            'prix' => 'required|numeric',
            'description' => 'required',
        ]);

            $article = new Article();
            $article->ref = $request->ref;
            $article->designation = $request->designation;
            $article->qte_stock = $request->stock;
            $article->qte_instance = $request->instance;
            $article->qte_reserve = $request->reserve;
            $article->prix = $request->prix;
            $article->description = $request->description;

           // $article->save();

            $article->description = $request->description;

            if($article->save()){
                return redirect()->route('articles')->with('success', 'Article created successfully.');
            }
            else {
                return back()->with('error', 'Failed to create article.');
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
        if($article->update()){
            return redirect()->route('articles')->with('success', 'Article updated successfully.');
        }
        else {
            return back()->with('error', 'Failed to update article.');
        }
    }

    public function importCSV(Request $request)
    {
        ini_set('max_execution_time', 1800);
        $request->validate([
            'import_csv' => 'required',
        ]);
        // dd($request->import_csv);
        //read csv file and skip data
        $file = $request->file('import_csv');
        $handle = fopen($file->path(), 'r');

        //skip the header row
        fgetcsv($handle);

        $chunksize = 25;
        while(!feof($handle))
        {
            $chunkdata = [];

            for($i = 0; $i<$chunksize; $i++)
            {
                $data = fgetcsv($handle,0, ';');
                if($data === false)
                {
                    break;
                }
                $chunkdata[] = $data;
            }
            $this->getchunkdata($chunkdata);

        }
        fclose($handle);

        return redirect()->route('articles')->with('success', 'Article updated successfully.');
    }
    public function getchunkdata($chunkdata)
    {
        foreach($chunkdata as $column){

            $ref = $column[0];

            $designation = $column[1];
            $prix  = $column[2];
            $qte_stock = $column[3];
            $qte_instance = $column[4];
            $qte_reserve = $column[5];

            $description ='*';

            $article = Article::where('ref',  $ref)->get();
          //  dd($article!=null);
            if ($article->isempty($article))
            {
                $article = new Article();
                $article->ref = $ref;
                $article->designation = $designation;
                $article->prix = $prix;
                $article->qte_stock = $qte_stock;
                $article->qte_instance = $qte_instance;
                $article->qte_reserve = $qte_reserve;

                $article->description = $description;

                $article->save();
            }
            else
            {
                $article->ref = $ref;
            $article->designation = $designation;
            $article->prix = $prix;
            $article->qte_stock = $qte_stock;
            $article->qte_instance = $qte_instance;
            $article->qte_reserve = $qte_reserve;

            $article->description = $description;
            $article->replace($article);
            }
        }
    }

    public function examples(){

      $articleRefs = ['KAMO 27D04', 'KAMO 7096', 'KAMO 103001', 'KAMO 112019', 'FARE DW029', 'FARE TB210', 'FARE K15721', 'FARE 23873'];
      $products = Article::whereIn('ref', $articleRefs)->get();
       return view('welcome')->with('products', $products);
   }



    public function recherche(Request $request){
        // $articles = Article::all();
        if ($request)
        {
         $key = trim($request->get('q'));

         //  $articles = article::query()
           $articles = article::where('ref', 'like', "%{$key}%")->orderBy('ref','ASC')


              ;
        }
        else
        {
         $articles = Article::orderBy('ref','ASC');
        }

         return view('admin.articles.index')->with('articles',$articles ->paginate(20)->withQueryString());
     }
}
