<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Article;
use Illuminate\Support\Facades\Log;

class ImportCsv implements ShouldQueue
{   public $timeout = 1800;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $rowCount;
    /**
     * Create a new job instance.
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle()
{
    $this->rowCount = 0;

    \Log::info('Making new directory');

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

    fgetcsv($handle, 0, ';');

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
                $arti = new Article();
                $arti->ref = $ref;
                $arti->designation = iconv("MS-ANSI", 'UTF-8', $designation);
                $arti->prix = $prix;
                $arti->qte_stock = $qte_stock;
                $arti->qte_instance = $qte_instance;
                $arti->qte_reserve = $qte_reserve;
                $arti->description = '*';
                $arti->save();
            } else {
                // Check if any of the fields have changed
                if (
                    $articles->designation != iconv("MS-ANSI", 'UTF-8', $designation) ||
                    round($articles->prix, 3, PHP_ROUND_HALF_UP) != round($prix, 3, PHP_ROUND_HALF_UP) ||
                    round($articles->qte_stock, 3, PHP_ROUND_HALF_UP) != round($qte_stock, 3, PHP_ROUND_HALF_UP) ||
                    round($articles->qte_instance, 3, PHP_ROUND_HALF_UP) != round($qte_instance, 3, PHP_ROUND_HALF_UP) ||
                    round($articles->qte_reserve, 3, PHP_ROUND_HALF_UP) != round($qte_reserve, 3, PHP_ROUND_HALF_UP)
                ) {
                    print_r('   ');
                    print_r($ref);

                    $articles->designation = iconv("MS-ANSI", 'UTF-8', $designation);
                    $articles->prix = $prix;
                    $articles->qte_stock = $qte_stock;
                    $articles->qte_instance = $qte_instance;
                    $articles->qte_reserve = $qte_reserve;
                    $articles->description = '*';
                    $articles->save();
                }
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
