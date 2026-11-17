<?php

namespace App\Http\Controllers;

use App\Jobs\SyncProductsFromIdempiere;
use App\Models\Article;
use App\Models\Client;
use App\Services\IdempiereService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * ErpSyncController
 *
 * Admin-only controller for the iDempiere sync dashboard.
 * All routes require auth + admin middleware (applied in web.php).
 */
class ErpSyncController extends Controller
{
    public function __construct(protected IdempiereService $erp) {}

    // -------------------------------------------------------------------------
    // GET /admin/erp/status
    // -------------------------------------------------------------------------

    /**
     * Show the ERP sync status dashboard.
     */
    public function status()
    {
        $lastReport  = Cache::get('erp_last_sync_report');
        $syncRunning = Cache::get('erp_sync_running', false);
        $apiPing     = $this->erp->ping();

        // Quick stats from the local DB
        $totalArticles = Article::count();
        $erpSynced     = Article::whereNotNull('erp_product_id')->count();
        $lastSyncedAt  = Article::whereNotNull('erp_synced_at')->max('erp_synced_at');

        // Client stats
        $totalClients     = Client::count();
        $clientsErpSynced = Client::whereNotNull('erp_synced_at')->count();
        $lastClientSync   = Client::whereNotNull('erp_synced_at')->max('erp_synced_at');

        return view('admin.erp.status', compact(
            'lastReport',
            'syncRunning',
            'apiPing',
            'totalArticles',
            'erpSynced',
            'lastSyncedAt',
            'totalClients',
            'clientsErpSynced',
            'lastClientSync'
        ));
    }

    // -------------------------------------------------------------------------
    // POST /admin/erp/sync
    // -------------------------------------------------------------------------

    /**
     * Trigger an immediate product sync (dispatched to the queue).
     */
    public function triggerSync(Request $request)
    {
        if (Cache::get('erp_sync_running')) {
            return back()->with('warning', 'A sync is already in progress. Please wait.');
        }

        $dryRun = (bool) $request->boolean('dry_run');

        SyncProductsFromIdempiere::dispatch($dryRun);

        $msg = $dryRun
            ? 'Dry-run sync dispatched. Check your logs for results.'
            : 'Product sync dispatched to the queue. Refresh in a moment to see results.';

        return back()->with('success', $msg);
    }

    // -------------------------------------------------------------------------
    // POST /admin/erp/sync-clients
    // -------------------------------------------------------------------------

    /**
     * Trigger a synchronous client sync from iDempiere.
     */
    public function syncClients()
    {
        if (Cache::get('erp_client_sync_running')) {
            return back()->with('warning', 'A client sync is already in progress.');
        }

        Cache::put('erp_client_sync_running', true, 300);

        try {
            Artisan::call('erp:sync-clients');
            $output = Artisan::output();
            Cache::forget('erp_client_sync_running');
            return back()->with('success', 'Client sync complete! ' . trim(strip_tags($output)));
        } catch (\Exception $e) {
            Cache::forget('erp_client_sync_running');
            return back()->with('error', 'Client sync failed: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // GET /admin/erp/products
    // -------------------------------------------------------------------------

    /**
     * Return a paginated JSON list of products as seen from iDempiere REST API.
     */
    public function browseErpProducts(Request $request)
    {
        $page  = max(1, (int) $request->get('page', 1));
        $limit = min(100, max(1, (int) $request->get('limit', 20)));

        try {
            $result = $this->erp->getProducts($page, $limit);

            return response()->json([
                'status'     => 'ok',
                'page'       => $page,
                'limit'      => $limit,
                'page_count' => $result['page-count'] ?? 1,
                'records'    => $result['records'] ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error'  => $e->getMessage(),
            ], 500);
        }
    }
    
    // -------------------------------------------------------------------------
    // GET /admin/erp/progress
    // -------------------------------------------------------------------------
    
    /**
     * Return the current progress of the background sync job.
     */
    public function progress()
    {
        $running = Cache::get('erp_sync_running', false);
        $progress = Cache::get('erp_sync_progress');

        return response()->json([
            'running' => $running,
            'progress' => $progress ?: [
                'current' => 0,
                'total' => 0,
                'percentage' => 0
            ]
        ]);
    }
    /**
     * Import a single chunk of products (called by the browser in a loop).
     * Fetches prices & stock from iDempiere in parallel for each batch.
     */
    public function syncBatch(Request $request)
    {
        $page   = max(1, (int) $request->input('page', 1));
        $limit  = 100;
        $dryRun = (bool) $request->boolean('dry_run');

        try {
            $baseUrl      = rtrim((string) config('idempiere.base_url'), '/');
            $token        = $this->erp->getToken();
            $priceVersion = config('idempiere.price_list_version_id', 1000000);

            // 1. Fetch products for this page
            $response = $this->erp->getProducts($page, $limit);
            $records  = $response['records'] ?? (is_array($response) && isset($response[0]) ? $response : []);

            if (empty($records)) {
                return response()->json(['done' => true, 'imported_in_batch' => 0]);
            }

            // 2. Collect ERP product IDs
            $productIds = array_values(array_filter(array_map(function ($row) {
                return (int) ($row['id'] ?? 0);
            }, $records)));
            $idList = implode(',', $productIds);

            // 3. Fetch prices & stocks in parallel
            $poolResponses = \Illuminate\Support\Facades\Http::pool(function ($pool) use ($baseUrl, $token, $idList, $priceVersion, $productIds) {
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
                    ]),
                ];
            });

            $prices = [];
            foreach ($poolResponses['prices']->json('records') ?? [] as $pRow) {
                $pid = $pRow['M_Product_ID']['id'] ?? null;
                if ($pid) $prices[(int)$pid] = (float) ($pRow['PriceStd'] ?? $pRow['PriceList'] ?? 0);
            }

            $stocks = [];
            foreach ($poolResponses['stocks']->json('records') ?? [] as $sRow) {
                $pid = $sRow['M_Product_ID']['id'] ?? null;
                if ($pid) $stocks[(int)$pid] = ($stocks[(int)$pid] ?? 0) + (float) ($sRow['QtyOnHand'] ?? 0);
            }

            // 4. Bulk upsert into articles
            $now      = now()->toDateTimeString();
            $bulkData = [];
            foreach ($records as $row) {
                $pid      = (int) ($row['id'] ?? 0);
                $sku      = $row['Value'] ?? $row['value'] ?? null;
                $name     = $row['Name'] ?? $row['name'] ?? 'Car Part';
                $desc     = $row['Description'] ?? $row['description'] ?? '';
                $category = $row['M_Product_Category_ID']['identifier'] ?? null;

                if (!$sku) continue;

                $bulkData[] = [
                    'ref'            => $sku,
                    'erp_product_id' => $pid ?: null,
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

            if (!$dryRun && !empty($bulkData)) {
                \App\Models\Article::upsert(
                    $bulkData,
                    ['ref'],
                    ['erp_product_id', 'designation', 'description', 'erp_category', 'qte_stock', 'prix', 'erp_synced_at', 'updated_at']
                );
            }

            return response()->json([
                'success'           => true,
                'done'              => count($records) < $limit,
                'page'              => $page,
                'imported_in_batch' => count($bulkData),
                'sample_name'       => $records[0]['Name'] ?? $records[0]['name'] ?? 'Car Part',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function syncStock(\App\Services\IdempiereProductSync $sync)
    {
        $result = $sync->syncStockOnly();
        return response()->json($result);
    }
}
