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
        $article->description = $request->description;
        if($article->update()){
            return redirect()->route('articles')->with('success', 'Article updated successfully.');
        }
        else {
            return back()->with('error', 'Failed to update article.');
        }
    }
}
