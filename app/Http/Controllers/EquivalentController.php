<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\equivalent;
use Illuminate\Http\Request;
use App\Models\Origin;
use Exception;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ImportEquivalent;
use Illuminate\Support\Facades\Log;


class EquivalentController extends Controller
{
    //

     public function importcsv()
    {
          $filePath = storage_path("csvFiles/Equiv.CSV");
         
        

        if (!file_exists($filePath)) {
            return back()->with('error', 'Fichier CSV non trouvé.');
        }
        // Dispatch le job
        
       ImportEquivalent::dispatch($filePath);
       
            
        // Log pour vérification
         //dd($filePath);
        return back()->with('success', 'Importation lancée en arrière-plan.');
    }
}
