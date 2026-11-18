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
                // withTokenRetry: always the current JWT, renewed once on 401 (it can expire mid-sync)
                $poolResponses = $this->withTokenRetry(fn (string $token) => Http::pool(function (Pool $pool) use ($baseUrl, $token, $idList, $priceVersion, $productIds) {
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
                }));

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
     * Stock-only sync (every 10 minutes and the "Actualiser Stock" button).
     *
     * Reads every m_storageonhand page (8 in parallel), then applies all quantities in a
     * few batched UPDATEs inside one transaction. ~12k storage rows: seconds, not minutes.
     */
    public function syncStockOnly(): array
    {
        set_time_limit(0);
        $startTime = microtime(true);

        try {
            [$stockMapById, $stockMapBySku] = $this->fetchStorage();

            $now = now()->toDateTimeString();
            $updatedCount = 0;

            DB::transaction(function () use ($stockMapById, $stockMapBySku, $now, &$updatedCount) {
                // 1. Match by iDempiere product ID
                $updatedCount += $this->batchUpdateStock('erp_product_id', $stockMapById, $now);

                // 2. Fallback by SKU, only for articles that have no ERP product ID
                $refsWithoutId = Article::whereNull('erp_product_id')
                    ->whereIn('ref', array_keys($stockMapBySku))
                    ->pluck('ref')->all();
                $updatedCount += $this->batchUpdateStock(
                    'ref', array_intersect_key($stockMapBySku, array_flip($refsWithoutId)), $now
                );

                // 3. Articles with stock locally but no longer in iDempiere storage -> 0.
                //    Everything matched above was stamped with $now, so "not stamped" = gone.
                if (!empty($stockMapById)) {
                    Article::where('qte_stock', '>', 0)
                        ->where(fn ($q) => $q->whereNull('erp_synced_at')->orWhere('erp_synced_at', '<', $now))
                        ->update(['qte_stock' => 0, 'erp_synced_at' => $now]);
                }
            });

            return [
                'success'             => true,
                'storage_items_found' => count($stockMapById),
                'articles_updated'    => $updatedCount,
                'duration_ms'         => round((microtime(true) - $startTime) * 1000),
            ];

        } catch (\Throwable $e) {
            Log::error('[Stock Sync Error] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Read all of m_storageonhand. Pages are fetched 8 at a time; a 401 renews the
     * iDempiere login once. Any other failed page aborts the whole sync, because a
     * partial map would zero the stock of every article on the pages we never read.
     *
     * @return array{0: array<int,float>, 1: array<string,float>} [qty by product ID, qty by SKU]
     */
    protected function fetchStorage(): array
    {
        $baseUrl  = rtrim((string) config('idempiere.base_url'), '/');
        $pageSize = 100;
        $parallel = 8;
        $byId = [];
        $bySku = [];
        $page = 0;

        while (true) {
            $pages = range($page, $page + $parallel - 1);
            $responses = $this->withTokenRetry(fn (string $token) => Http::pool(fn (Pool $pool) => array_map(
                fn ($p) => $pool->as("p{$p}")->withToken($token)->timeout(30)->get("{$baseUrl}/models/m_storageonhand", [
                    '$select'  => 'M_Product_ID,QtyOnHand',
                    '$orderby' => 'M_StorageOnHand_UU',
                    '$top'     => $pageSize,
                    '$skip'    => $p * $pageSize,
                ]),
                $pages
            )));

            foreach ($pages as $p) {
                $res = $responses["p{$p}"];
                if ($res instanceof \Throwable) {
                    throw new Exception('m_storageonhand page ' . ($p + 1) . ' failed: ' . $res->getMessage());
                }
                if (!$res->successful()) {
                    throw new Exception('m_storageonhand page ' . ($p + 1) . " failed ({$res->status()})");
                }

                $records = $res->json('records') ?? [];
                foreach ($records as $row) {
                    $pid = $row['M_Product_ID']['id'] ?? null;
                    $identifier = $row['M_Product_ID']['identifier'] ?? '';
                    $qty = (float) ($row['QtyOnHand'] ?? 0);

                    if ($pid) {
                        $byId[(int) $pid] = ($byId[(int) $pid] ?? 0) + $qty;
                    }
                    // SKU from the identifier, e.g. "001 AZ2018_REGULATEUR..."
                    if ($identifier !== '') {
                        $sku = trim(explode('_', $identifier)[0]);
                        $bySku[$sku] = ($bySku[$sku] ?? 0) + $qty;
                    }
                }

                if (count($records) < $pageSize) {
                    return [$byId, $bySku];   // last page reached
                }
            }

            $page += $parallel;
        }
    }

    /**
     * Run a pool of requests; if any came back 401 (expired JWT), renew the token and run it once more.
     */
    protected function withTokenRetry(callable $send): array
    {
        $responses = $send($this->erp->getToken());

        foreach ($responses as $res) {
            if (!($res instanceof \Throwable) && $res->status() === 401) {
                return $send($this->erp->refreshToken());
            }
        }

        return $responses;
    }

    /**
     * UPDATE articles SET qte_stock = CASE <column> WHEN ... END in chunks of 500.
     */
    protected function batchUpdateStock(string $column, array $quantities, string $now): int
    {
        if (!in_array($column, ['erp_product_id', 'ref'], true)) {
            throw new \InvalidArgumentException("Unexpected column {$column}");
        }

        $affected = 0;

        foreach (array_chunk($quantities, 500, true) as $chunk) {
            $cases = [];
            $bindings = [];
            foreach ($chunk as $key => $qty) {
                $cases[] = 'WHEN ? THEN ?';
                array_push($bindings, $key, $qty);
            }
            $bindings[] = $now;
            array_push($bindings, ...array_keys($chunk));
            $in = implode(',', array_fill(0, count($chunk), '?'));

            $affected += DB::update(
                "UPDATE articles SET qte_stock = CASE {$column} " . implode(' ', $cases) . " END, erp_synced_at = ? WHERE {$column} IN ({$in})",
                $bindings
            );
        }

        return $affected;
    }
}
