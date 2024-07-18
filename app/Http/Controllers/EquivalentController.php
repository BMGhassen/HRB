<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\equivalent;
use Illuminate\Http\Request;

class EquivalentController extends Controller
{
    //

    public function importCSV(Request $request)
    {   ini_set('max_execution_time', 1800);
        $request->validate([
            'import_csv1' => 'required',
            'prefix' => 'required'
        ]);

        //  dd($request->prefix);
        //read csv file and skip data
        $file = $request->file('import_csv1');
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

            $ref = $column[1];
            $marque  = $column[2];
            $articles = Article::where('ref',$prefix." ".$OurRef )->get();


            if($articles->isNotEmpty($articles))
            {
                $equivalent = equivalent::where('ref',  $ref)->where('marque', $marque)->where('OurRef',$prefix." ".$OurRef)->get();
          //  dd($article!=null);
            if ($equivalent->isempty($equivalent))
            {
                $equivalent = new equivalent();
                $equivalent->ref = $ref;
                $equivalent->OurRef = $prefix." ".$OurRef;
                $equivalent->marque = $marque;
                $equivalent->prod_id = $articles->first()->id;

                $equivalent->save();
            }

            }


        }
    }
}
