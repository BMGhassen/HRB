<?php

namespace App\Http\Controllers;

use App\Models\Origin;
use App\Models\Article;
use Illuminate\Http\Request;
use Exception;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ImportOrigin;
use Illuminate\Support\Facades\Log;


class OriginController extends Controller
{
    //
   
    public function importcsv()
    {
          $filePath = storage_path("csvFiles/Origine.CSV");
         
        

        if (!file_exists($filePath)) {
            return back()->with('error', 'Fichier CSV non trouvé.');
        }
        // Dispatch le job
        
       ImportOrigin::dispatch($filePath);
       
            
        // Log pour vérification
         //dd($filePath);
        return back()->with('success', 'Importation lancée en arrière-plan.');
    }
}
