<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Pool;
use Exception;

class IdempiereProductSync
{
    public function __construct(protected IdempiereService $erp) {}

    public function sync(bool $dryRun = false, int $limit = 0, bool $fullSync = false): array
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        DB::disableQueryLog();

        $startTime = microtime(true);
        $report = [
            'total'       => 0,
            'created'     => 0,
            'updated'     => 0,
            'skipped'     => 0,
            'errors'      => 0,
            'dry_run'     => $dryRun,
            'synced_at'   => now()->toDateTimeString(),
            'duration_ms' => 0,
            'error_log'   => [],
        ];

        try {
            $batchSize = 100;
            $page = 1;
            $now = now()->toDateTimeString();
            $token = $this->erp->getToken();
            $baseUrl = rtrim((string) config('idempiere.base_url'), '/');
            $priceVersion = config('idempiere.price_list_version_id', 1000000);

            // 1. Delta Sync Filter (Only fetch modified parts since last sync)
            $lastSync = Cache::get('erp_last_sync_timestamp');
            $filter = (!$fullSync && $lastSync) ? "Updated gt '{$lastSync}'" : null;

            do {
                // Fetch products page
                $params = [
                    '$top'     => $batchSize,
                    '$skip'    => ($page - 1) * $batchSize,
                    '$orderby' => 'Value asc'
                ];
                if ($filter) {
                    $params['$filter'] = $filter;
                }

                $response = $this->erp->getModel('m_product', $params);
                $records  = $response['records'] ?? [];

                if (empty($records)) {
                    break;
                }

                // Collect IDs for parallel price/stock queries
                $productIds = array_filter(array_map(function ($row) {
                    return (int) ($row['id'] ?? $row['M_Product_ID']['id'] ?? $row['M_Product_ID'] ?? 0);
                }, $records));

                $idList = implode(',', $productIds);

                // 2. PARALLEL HTTP POOL (Fetches Prices & Stock simultaneously!)
                $poolResponses = Http::pool(function (Pool $pool) use ($baseUrl, $token, $idList, $priceVersion, $productIds) {
                    return [
                        $pool->as('prices')->withToken($token)->get("{$baseUrl}/models/m_productprice", [
                            '$filter' => "M_Product_ID in ({$idList}) and M_PriceList_Version_ID eq {$priceVersion}",
                            '$select' => 'M_Product_ID,PriceStd,PriceList',
                            '$top'    => count($productIds) * 2,
                        ]),
                        $pool->as('stocks')->withToken($token)->get("{$baseUrl}/models/m_storageonhand", [
                            '$filter' => "M_Product_ID in ({$idList})",
                            '$select' => 'M_Product_ID,QtyOnHand',
                            '$top'    => count($productIds) * 5,
                        ])
                    ];
                });

                // A failed price/stock call must stop the sync: otherwise every product in
                // this batch would be saved with price 0 and stock 0.
                foreach (['prices', 'stocks'] as $part) {
                    $res = $poolResponses[$part];
                    if ($res instanceof \Throwable) {
                        throw new Exception("iDempiere {$part} request failed: " . $res->getMessage());
                    }
                    if (!$res->successful()) {
                        throw new Exception("iDempiere {$part} request failed ({$res->status()}) on page {$page}");
                    }
                }

                // Parse Parallel Prices
                $prices = [];
                $priceRecords = $poolResponses['prices']->json('records') ?? [];
                foreach ($priceRecords as $pRow) {
                    $pid = $pRow['M_Product_ID']['id'] ?? null;
                    if ($pid) $prices[(int)$pid] = (float) ($pRow['PriceStd'] ?? $pRow['PriceList'] ?? 0);
                }

                // Parse Parallel Stocks
                $stocks = [];
                $stockRecords = $poolResponses['stocks']->json('records') ?? [];
                foreach ($stockRecords as $sRow) {
                    $pid = $sRow['M_Product_ID']['id'] ?? null;
                    if ($pid) $stocks[(int)$pid] = ($stocks[(int)$pid] ?? 0) + (float) ($sRow['QtyOnHand'] ?? 0);
                }

                // Build bulk upsert array
                $bulkData = [];
                foreach ($records as $row) {
                    $pid      = (int) ($row['id'] ?? $row['M_Product_ID']['id'] ?? $row['M_Product_ID'] ?? 0);
                    $sku      = $row['Value'] ?? $row['value'] ?? null;
                    $name     = $row['Name'] ?? $row['name'] ?? 'Car Part';
                    $desc     = $row['Description'] ?? $row['description'] ?? '';
                    $category = $row['M_Product_Category_ID']['identifier'] ?? null;

                    // erp_product_id is the key we match on: without it (or a SKU) we can't sync
                    if (!$sku || !$pid) {
                        $report['skipped']++;
                        continue;
                    }

                    $report['total']++;
                    $bulkData[] = [
                        'ref'            => $sku,
                        'erp_product_id' => $pid,
                        'designation'    => $name,
                        'description'    => $desc ?: $name,
                        'erp_category'   => $category,
                        'qte_stock'      => $stocks[$pid] ?? 0,
                        'qte_instance'   => 0,
                        'qte_reserve'    => 0,
                        'prix'           => $prices[$pid] ?? 0,
                        'erp_synced_at'  => $now,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }

                // Single bulk SQL query
                if (!$dryRun && !empty($bulkData)) {
                    // Match on erp_product_id: it is the only UNIQUE key (ref is not), and it is
                    // what MySQL's ON DUPLICATE KEY already used in practice.
                    Article::upsert(
                        $bulkData,
                        ['erp_product_id'],
                        ['ref', 'designation', 'description', 'erp_category', 'qte_stock', 'prix', 'erp_synced_at', 'updated_at']
                    );
                }

                // Update Progress Bar
                $totalEstimated = max(47669, $report['total']);
                $percentage = min(99, round(($report['total'] / $totalEstimated) * 100));

                Cache::put('erp_sync_progress', [
                    'current'    => $report['total'],
                    'total'      => $totalEstimated,
                    'percentage' => $percentage,
                ], 300);

                if (count($records) < $batchSize) {
                    break;
                }

                $page++;
            } while (true);

            // Save the timestamp of this sync for future Delta syncs!
            Cache::put('erp_last_sync_timestamp', $now, now()->addYear());

            Cache::put('erp_sync_progress', [
                'current'    => $report['total'],
                'total'      => $report['total'],
                'percentage' => 100,
            ], 300);

        } catch (Exception $e) {
            $report['errors']++;
            $report['error_log'][] = 'Fatal sync error: ' . $e->getMessage();
            Log::error('[ERP Sync] ' . $e->getMessage());
        }

        $report['duration_ms'] = round((microtime(true) - $startTime) * 1000);
        return $report;
    }
    /**
     * Ultra-fast Stock-Only Sync (Runs in ~500ms!)
     */
 public function syncStockOnly(): array
    {
        $startTime = microtime(true);
        $updatedCount = 0;
        $baseUrl = rtrim((string) config('idempiere.base_url'), '/');
        $token = $this->erp->getToken();

        try {
            $page = 1;
            $stockMapById = [];
            $stockMapBySku = [];

            do {
                // Fetch storage records page by page
                $response = Http::withToken($token)
                    ->timeout(15)
                    ->get("{$baseUrl}/models/m_storageonhand", [
                        '$select' => 'M_Product_ID,QtyOnHand',
                        '$top'    => 100,
                        '$skip'   => ($page - 1) * 100
                    ]);

                // A failed page must abort: with a partial map, the "zero out" step below
                // would set stock to 0 for every article on the pages we never read.
                if (!$response->successful()) {
                    throw new Exception("m_storageonhand page {$page} failed ({$response->status()})");
                }

                $records = $response->json('records') ?? [];

                if (empty($records)) {
                    break;
                }

                foreach ($records as $row) {
                    $pid        = $row['M_Product_ID']['id'] ?? null;
                    $identifier = $row['M_Product_ID']['identifier'] ?? '';
                    $qty        = (float) ($row['QtyOnHand'] ?? 0);

                    // 1. Map by Product ID
                    if ($pid) {
                        $stockMapById[(int)$pid] = ($stockMapById[(int)$pid] ?? 0) + $qty;
                    }

                    // 2. Map by SKU (Extract SKU from identifier e.g. "001 AZ2018_REGULATEUR...")
                    if (!empty($identifier)) {
                        $sku = trim(explode('_', $identifier)[0]);
                        $stockMapBySku[$sku] = ($stockMapBySku[$sku] ?? 0) + $qty;
                    }
                }

                if (count($records) < 100) {
                    break;
                }

                $page++;
            } while (true);

            // Update MySQL articles table using Product ID FIRST, then fallback to SKU!
            foreach ($stockMapById as $erpId => $qty) {
                $affected = Article::where('erp_product_id', $erpId)
                    ->update(['qte_stock' => $qty, 'erp_synced_at' => now()]);

                if ($affected) {
                    $updatedCount += $affected;
                }
            }

            // Fallback update for any items matched by SKU
            foreach ($stockMapBySku as $sku => $qty) {
                Article::where('ref', $sku)
                    ->where('qte_stock', '!=', $qty)
                    ->update(['qte_stock' => $qty, 'erp_synced_at' => now()]);
            }

            // Zero out any articles with positive stock locally that no longer exist in ERP storage
            if (!empty($stockMapById)) {
                Article::where('qte_stock', '>', 0)
                    ->whereNotIn('erp_product_id', array_keys($stockMapById))
                    ->whereNotIn('ref', array_keys($stockMapBySku))
                    ->update(['qte_stock' => 0, 'erp_synced_at' => now()]);
            }

            return [
                'success'           => true,
                'storage_items_found' => count($stockMapById),
                'articles_updated'  => $updatedCount,
                'duration_ms'       => round((microtime(true) - $startTime) * 1000)
            ];

        } catch (Exception $e) {
            Log::error('[Stock Sync Error] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}