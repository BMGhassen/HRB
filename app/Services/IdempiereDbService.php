<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * IdempiereDbService
 *
 * Reads iDempiere data DIRECTLY from the PostgreSQL database
 * using the named 'idempiere' connection defined in config/database.php.
 *
 * Use this when the iDempiere REST API plugin is not installed.
 * It queries the same tables the REST API would, just via PDO instead of HTTP.
 */
class IdempiereDbService
{
    protected int $warehouseId;
    protected int $priceListVersionId;
    protected int $batchSize;

    public function __construct()
    {
        $this->warehouseId        = (int) config('idempiere.warehouse_id', 114);
        $this->priceListVersionId = (int) config('idempiere.price_list_version_id', 101);
        $this->batchSize          = (int) config('idempiere.sync_batch_size', 100);
    }

    // -------------------------------------------------------------------------
    // Connection Test
    // -------------------------------------------------------------------------

    /**
     * Test the direct DB connection and return a status array.
     */
    public function ping(): array
    {
        try {
            $count = DB::connection('idempiere')->table('m_product')->count();
            return [
                'connected'     => true,
                'method'        => 'Direct PostgreSQL',
                'product_count' => $count,
                'warehouse_id'  => $this->warehouseId,
                'price_list_version_id' => $this->priceListVersionId,
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'method'    => 'Direct PostgreSQL',
                'error'     => $e->getMessage(),
            ];
        }
    }

    // -------------------------------------------------------------------------
    // Products
    // -------------------------------------------------------------------------

    /**
     * Fetch ALL active, stocked products with their category names in one query.
     * Returns a flat array of stdClass rows.
     *
     * @param  int  $limit  0 = all
     * @return array
     */
    public function getAllProducts(int $limit = 0): array
    {
        $query = DB::connection('idempiere')
            ->table('m_product as p')
            ->leftJoin('m_product_category as pc', 'p.m_product_category_id', '=', 'pc.m_product_category_id')
            ->select(
                'p.m_product_id',
                'p.value',
                'p.name',
                'p.description',
                'p.producttype',
                'p.isactive',
                'pc.name as category_name'
            )
            ->where('p.isactive', 'Y')
            ->where('p.producttype', 'I') // 'I' = stocked Item
            ->orderBy('p.value');

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get()->toArray();
    }

    // -------------------------------------------------------------------------
    // Stock
    // -------------------------------------------------------------------------

    /**
     * Fetch summed on-hand stock for all products in the configured warehouse.
     * Returns associative array: [ m_product_id => qty_on_hand ]
     *
     * @return array<int, float>
     */
    public function getStockMap(): array
    {
        $rows = DB::connection('idempiere')
            ->table('m_storageonhand as s')
            ->join('m_locator as l', 's.m_locator_id', '=', 'l.m_locator_id')
            ->select('s.m_product_id', DB::raw('SUM(s.qtyonhand) as total_qty'))
            ->where('l.m_warehouse_id', $this->warehouseId)
            ->groupBy('s.m_product_id')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->m_product_id] = (float) $row->total_qty;
        }
        return $map;
    }

    // -------------------------------------------------------------------------
    // Prices
    // -------------------------------------------------------------------------

    /**
     * Fetch standard prices for all products from the configured price list version.
     * Returns associative array: [ m_product_id => pricestd ]
     *
     * @return array<int, float>
     */
    public function getPriceMap(): array
    {
        // First, try to auto-detect the price list version if the configured one returns nothing
        $rows = DB::connection('idempiere')
            ->table('m_productprice')
            ->select('m_product_id', 'pricestd')
            ->where('m_pricelist_version_id', $this->priceListVersionId)
            ->get();

        // If nothing found with configured ID, try to find the first available version
        if ($rows->isEmpty()) {
            Log::warning("[iDempiere DB] No prices found for price list version {$this->priceListVersionId}. Trying to auto-detect...");
            $rows = $this->autoDetectPrices();
        }

        $map = [];
        foreach ($rows as $row) {
            $map[$row->m_product_id] = (float) $row->pricestd;
        }
        return $map;
    }

    /**
     * Try to find ANY active price list version and return its prices.
     */
    private function autoDetectPrices(): \Illuminate\Support\Collection
    {
        // Find the most recently created price list version
        $versionId = DB::connection('idempiere')
            ->table('m_pricelist_version')
            ->where('isactive', 'Y')
            ->orderByDesc('created')
            ->value('m_pricelist_version_id');

        if (!$versionId) {
            Log::warning('[iDempiere DB] No active price list versions found.');
            return collect();
        }

        Log::info("[iDempiere DB] Auto-detected price list version: {$versionId}. Update IDEMPIERE_PRICE_LIST_VERSION_ID in .env.");

        return DB::connection('idempiere')
            ->table('m_productprice')
            ->select('m_product_id', 'pricestd')
            ->where('m_pricelist_version_id', $versionId)
            ->get();
    }

    // -------------------------------------------------------------------------
    // Helper: All data in one pass (optimised for sync)
    // -------------------------------------------------------------------------

    /**
     * Fetch products, stock map, and price map in three efficient queries.
     * Returns: [ 'products' => [...], 'stock' => [...], 'prices' => [...] ]
     */
    public function fetchAll(int $limit = 0): array
    {
        $products = $this->getAllProducts($limit);
        $stock    = $this->getStockMap();
        $prices   = $this->getPriceMap();

        return compact('products', 'stock', 'prices');
    }
}
