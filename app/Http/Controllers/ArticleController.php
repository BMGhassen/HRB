<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    //afficher la liste des articles
    public function index(){
        $articles = Article::all();
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
}
