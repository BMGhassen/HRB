<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Article;
use Illuminate\Support\Facades\Log;

class ImportCsvJob implements ShouldQueue
{
    public $timeout = 1800;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $rowCount;


    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        
       $this->rowCount = 0;


        \Log::info('Making new directory');
        //print_r('***');
    
        if (!file_exists($this->filePath)) {
           print_r('inexistant : '.$this->filePath);
            return;
        }
        print_r('======: '.$this->filePath);
        $handle = fopen($this->filePath, 'r');
        if (!$handle) {
            print_r('Impossible d\'ouvrir le fichier : ' . $this->filePath);
            return;
        }
    
        // Ignorer la première ligne si c'est un en-tête
        fgetcsv($handle, 0, ';');
   //  print_r('2222 ');
        $rowCount = 0;
        $successCount = 0;
        $errorCount = 0;
    
        while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
            $rowCount++;
          
            try {
                if (count($data) < 6) {
                    Log::warning("Ligne $rowCount : données incomplètes");
                    $errorCount++;
                    continue;
                }
    
                [$ref, $designation, $prix, $qte_stock, $qte_instance, $qte_reserve] = $data;
               
         
                $articles = Article::where('ref', $ref)->first();
              
                if ($articles === null) {
                   //  print_r('1');
                    $arti =new Article();
                    
                    $arti->ref = $ref;
                   
                    $arti->designation = iconv("MS-ANSI",'UTF-8', $designation);
                   
                    $arti->prix = $prix;
                  
                    $arti->qte_stock = $qte_stock;
                  
                    $arti->qte_instance = $qte_instance;
                      
                    $arti->qte_reserve = $qte_reserve;
    
                    $arti->description = '*';
    // print_r('5');
                    $arti->save();
                }
                else
                {
                   //   print_r('2');
                    $articles->ref = $ref;
                    $articles->designation = iconv("MS-ANSI",'UTF-8', $designation);
                    $articles->prix = $prix;
                    $articles->qte_stock = $qte_stock;
                    $articles->qte_instance = $qte_instance;
                    $articles->qte_reserve = $qte_reserve;
    
                    $articles->description = '*';
    
                    $articles->save();
                    
                }
                
                               
                                $successCount++;
                     print_r($successCount);
                      print_r('   ');
                                if ($rowCount % 100 == 0) {
                                    Log::info("$rowCount lignes traitées");
                                }
                            } catch (\Exception $e) {
                                Log::error("Erreur à la ligne $rowCount : " . $e->getMessage());
                                $errorCount++;
                            }
                        }      
                    
        fclose($handle);
    
        Log::info("Importation terminée. Total: $rowCount, Succès: $successCount, Erreurs: $errorCount");
    }
   
}