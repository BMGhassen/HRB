<?php

namespace App\Http\Controllers;

use App\Models\Origin;
use App\Models\Article;
use Illuminate\Http\Request;

class OriginController extends Controller
{
    //
    public function importCSV(Request $request)
    {   ini_set('max_execution_time', 1800);
        $request->validate([
            'import_csv2' => 'required',
            'prefix' => 'required'
        ]);

        //  dd($request->prefix);
        //read csv file and skip data
        $file = $request->file('import_csv2');
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
            $this->getchunkdata($chunkdata,$request->prefix);

        }
        fclose($handle);

        return redirect()->route('articles')->with('success', 'Article updated successfully.');
    }
    public function getchunkdata($chunkdata,$prefix)
    {
        foreach($chunkdata as $column){

            $OurRef = $column[0];

            $ref_O = $column[1];
            $marque  = $column[2];
            $articles = Article::where('ref',$prefix." ".$OurRef )->get();


            if($articles->isNotEmpty($articles))
            {
                $origins = Origin::where('ref_O',  $ref_O)->where('marque', $marque)->where('OurRef',$prefix." ".$OurRef)->get();
          //  dd($article!=null);
            if ($origins->isempty($origins))
            {
                $origins = new Origin();
                $origins->ref_O = $ref_O;
                $origins->OurRef = $prefix." ".$OurRef;
                $origins->marque = $marque;
                $origins->prod_id = $articles->first()->id;

                $origins->save();
            }

            }


        }
    }
}
