<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

class ImportClient implements ShouldQueue
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

         //      print_r('***');
       // \Log::info('Making new directory');
        
      
        if (!file_exists($this->filePath)) 
        {
         //      dd($this->filePath);
           print_r('inexistant : '.$this->filePath);
            return;
        }
        print_r('======: '.$this->filePath);
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
                
                if (count($data) < 9) 
                {
                    Log::warning("Ligne $rowCount : données incomplètes");
                    $errorCount++;
                    continue;
                }
               // dd($data);
                //Code;RS;Tel;MatF;Resp;Adresse;Region;Vendeur;
                 //dd(count($data));    
          //      print_r('  1 ');
                [$code, $rs, $tel, $MatF,  $resp,$Adresse, $Region ,$vendeur,$nn] = $data;
           //     print_r(' 2 ');
                                                 
                $clients = Client::where('code', $code)->first();

                if ($clients === null) 
                {
                   //  print_r('1');
                    $cli =new Client();

                    $cli->code = $code;

                    $cli->rs = $rs;

                    $cli->tel = $tel;

                    $cli->MatF = $MatF;

                    $cli->nom ='*';

                    $cli->resp = $resp;

                    $cli->Adresse = $Adresse;

                    $cli->Region = $Region;
                    $cli->save();
                }
                else
                {
                   //   print_r('2');
                    $clients->code = $code;
                    $clients->rs = $rs;
                    $clients->tel = $tel;
                    $clients->MatF = $MatF;
                    $clients->nom = '*';
                    $clients->resp = $resp;
                    $clients->Adresse = $Adresse;
                    $clients->Region = $Region;
                    
                //    $clients->vendeur_id = $vendeur_id;
                //    $clients->email = $email;
                    $cli->nom ='*';
                    $clients->save();

                }


                $successCount++;
            //   print_r($successCount);
             //   print_r('   ');
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
