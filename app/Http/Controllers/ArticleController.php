<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Article;
use App\Jobs\ProcessCsv;
use Illuminate\View\View;
use App\Models\equivalent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    //afficher la liste des articles
    private $nombrer=0;
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
        $this->nombrer=0;
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

        $chunksize =1000;
        while(!feof($handle))
        {   $output = new ConsoleOutput();
            $output->writeln($this->nombrer);
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
            $this->getchunkdata2($chunkdata);

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

            $this->nombrer++;

            $article = Article::where('ref',  $ref)->get();
        //   $artid = Article::select('id')
        //                    ->where('ref', '=',  $ref)
        //                    ->get();
          //  dd($article!=null);


            if ($article->isempty($article))
            //if ($artid->isempty($artid))
            {
                $output = new ConsoleOutput();
                $output->writeln('2');
                $arti =new Article();
                $arti->ref = $ref;
                $arti->designation = $designation;
                $arti->prix = $prix;
                $arti->qte_stock = $qte_stock;
                $arti->qte_instance = $qte_instance;
                $arti->qte_reserve = $qte_reserve;

                $arti->description = $description;

                $arti->save();
            }
            else
            {

                //$art= Article::find( $artid->first()->id);
             $art=$article->first();
            $art->designation = $designation;
            $art->prix = $prix;
            $art->qte_stock = $qte_stock;
            $art->qte_instance = $qte_instance;
            $art->qte_reserve = $qte_reserve;
            $art->save();
            }
        }
    }

    public function examples()
    {
      $products = Article::where('sel', '1')->get();
       return view('welcome')->with('products', $products);
   }

   public function updateSel(Request $request)
    {
        $id = $request->input('id');
        $sel = $request->input('sel');

        $item = Article::find($id);
        if ($item) {
            $item->sel = $sel;
            $item->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Item not found'], 404);
    }


    public function recherche(Request $request){
        // $articles = Article::all();
        if ($request)
        {
         $key = trim($request->get('q'));

         //  $articles = article::query()
           $articles = article::where('ref', 'like', "%{$key}%")->orderBy('ref','ASC');

        }
        else
        {
         $articles = Article::orderBy('ref','ASC');
        }

         return view('admin.articles.index')->with('articles',$articles ->paginate(20)->withQueryString());
     }
}
