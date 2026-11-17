<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Origin;
use App\Models\Article;
use Illuminate\Support\Facades\Log;

class ImportOrigin implements ShouldQueue
{
     public $timeout = 600;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {


        if (!file_exists($this->filePath))
        {
         //      dd($this->filePath);
           print_r('inexistant : '.$this->filePath);
            return;
        }
      //  print_r('======: '.$this->filePath);
        $handle = fopen($this->filePath, 'r');
        if (!$handle)
        {
             // dd('2');
            print_r('Impossible d\'ouvrir le fichier : ' . $this->filePath);
            return;
        }

        // Ignorer la première ligne si c'est un en-tête
        fgetcsv($handle, 0, ';');
      //  print_r('2222 ');
        $rowCount = 0;
        $successCount = 0;
        $errorCount = 0;

        while (($data = fgetcsv($handle, 0, ';')) !== FALSE)
        {
            $rowCount++;
           
 
        
              print_r($rowCount);
               print_r('   ');
            try
            {

                if (count($data) < 3)
                {
                    Log::warning("Ligne $rowCount : données incomplètes");
                    $errorCount++;
                    continue;
                }
               // dd($data);
                //ref_O;OurRef;marque;prod_id;Resp;Adresse;Region;Vendeur;
                 //dd(count($data));
          //      print_r('  1 ');
                [ $OurRef,$ref_O, $marque] = $data;
           //     print_r(' 2 ');
                $art=Article::where('ref', $OurRef)->first();
                if ($art!==null)
                {
                   // $Origins = Origin::where('ref_O', $ref_O)->first();
                    $Origins = Origin::where('ref_O',  $ref_O)->where('marque', $marque)->where('OurRef',$OurRef)->first();
        
    
                    if ($Origins === null)
                    {
                       //  print_r('1');
                        $og =new Origin();
    
                        $og->ref_O = $ref_O;
    
                        $og->OurRef = $OurRef;
    
                        $og->marque =  iconv("MS-ANSI",'UTF-8',$marque);
    
                        $og->prod_id = $art->id;
    
                        $og->save();
                    }
         
                }

                $successCount++;
           //   print_r($successCount);
           //    print_r('   ');
                if ($rowCount % 100 == 0)
                {
                      Log::info("$rowCount lignes traitées");
                }
            }
            catch (\Exception $e)
            {
               // dd($e->getMessage());
                                $errorCount++;
            }
        }


        fclose($handle);

    }
}
