<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * IdempiereService
 *
 * REST API client for iDempiere using the modern /api/v1 endpoints.
 * Handles JWT authentication (with 23-hour token caching), pagination,
 * and provides typed methods for fetching products, stock and prices.
 */
class IdempiereService
{
    protected string $baseUrl;
    protected int $clientId;
    protected int $roleId;
    protected int $orgId;
    protected int $warehouseId;
    protected string $language;
    protected string $username;
    protected string $password;
    protected int $timeout;

    // Cache key for the JWT token
    private const TOKEN_CACHE_KEY = 'idempiere_jwt_token';

    public function __construct()
    {
        $this->baseUrl     = rtrim((string) config('idempiere.base_url'), '/');
        $this->clientId    = config('idempiere.client_id', 11);
        $this->roleId      = config('idempiere.role_id', 102);
        $this->orgId       = config('idempiere.org_id', 11);
        $this->warehouseId = config('idempiere.warehouse_id', 114);
        $this->language    = config('idempiere.language', 'en_US');
        $this->username    = (string) config('idempiere.username');
        $this->password    = (string) config('idempiere.password');
        $this->timeout     = config('idempiere.timeout', 30);
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    /**
     * Returns a valid JWT token, fetching a fresh one if needed.
     * The token is cached for 23 hours (iDempiere tokens expire after 24h).
     */
    public function getToken(): string
    {
        $ttl = config('idempiere.token_ttl', 82800); // 23 hours in seconds

        return Cache::remember(self::TOKEN_CACHE_KEY, $ttl, function () {
            return $this->authenticate();
        });
    }

    /**
     * Perform the two-step JWT authentication against iDempiere REST API.
     *
     * Step 1: POST /auth/tokens  → receive a temporary token
     * Step 2: PUT  /auth/tokens  → set session context, receive final JWT
     */
       protected function authenticate(): string
    {
        if ($this->baseUrl === '' || $this->username === '' || $this->password === '') {
            throw new Exception('iDempiere is not configured: set IDEMPIERE_URL, IDEMPIERE_USERNAME and IDEMPIERE_PASSWORD in .env');
        }

        // Step 1 — Login
        $step1 = Http::timeout($this->timeout)
            ->post("{$this->baseUrl}/auth/tokens", [
                'userName' => $this->username,
                'password' => $this->password,
            ]);

        if (!$step1->successful()) {
            throw new Exception(
                "iDempiere auth step 1 failed ({$step1->status()}): {$step1->body()}"
            );
        }

        $token = $step1->json('token')
            ?? throw new Exception('iDempiere auth step 1: no token in response. Body: ' . $step1->body());

        // Step 2 — Set session parameters
        $step2 = Http::timeout($this->timeout)
            ->withToken($token)
            ->put("{$this->baseUrl}/auth/tokens", [
                'clientId'       => $this->clientId,
                'roleId'         => $this->roleId,
                'organizationId' => $this->orgId,
                'warehouseId'    => $this->warehouseId,
                'language'       => $this->language,
            ]);

        if (!$step2->successful()) {
            throw new Exception(
                "iDempiere auth step 2 failed ({$step2->status()}): {$step2->body()}"
            );
        }

        $token = $step2->json('token')
            ?? throw new Exception('iDempiere auth step 2: no token in response.');

        Log::info('[iDempiere] JWT token refreshed successfully.');

        return $token;
    }

    /**
     * Force-refresh the cached token (e.g. after a 401 response).
     */
    public function refreshToken(): string
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
        return $this->getToken();
    }

    // -------------------------------------------------------------------------
    // Generic Model Fetch
    // -------------------------------------------------------------------------

    /**
     * Fetch records from any iDempiere model endpoint with OData-style filtering.
     *
     * @param  string      $model   Table/model name, e.g. 'M_Product'
     * @param  array       $params  OData params: $filter, $select, $top, $skip, $orderby
     * @param  bool        $retry   Whether to retry once after a 401 (token refresh)
     * @return array
     */
    public function getModel(string $model, array $params = [], bool $retry = true): array
    {
        $response = Http::timeout($this->timeout)
            ->withToken($this->getToken())
            ->get("{$this->baseUrl}/models/{$model}", $params);

        // If unauthorized, refresh the token and retry once
        if ($response->status() === 401 && $retry) {
            $this->refreshToken();
            return $this->getModel($model, $params, false);
        }

        if (!$response->successful()) {
            throw new Exception(
                "iDempiere GET /models/{$model} failed ({$response->status()}): {$response->body()}"
            );
        }

        return $response->json() ?? [];
    }

    // -------------------------------------------------------------------------
    // Products
    // -------------------------------------------------------------------------

    /**
     * Fetch a page of active, stocked products from M_Product.
     *
     * @param  int   $page     1-based page number
     * @param  int   $limit    Records per page (max ~500 in iDempiere default config)
     * @return array{records: array, page-count: int, total: int}
     */
   public function getProducts(int $page = 1, int $limit = 100): array
    {
        $skip = ($page - 1) * $limit;

        // Using lowercase m_product for TrekGlobal REST compatibility
        $response = $this->getModel('m_product', [
            '$top'     => $limit,
            '$skip'    => $skip,
            '$orderby' => 'Value asc',
        ]);

        // Normalize response whether it's wrapped in 'records' or a direct array
        if (isset($response['records'])) {
            return $response;
        }

        if (is_array($response) && isset($response[0])) {
            return [
                'records'    => $response,
                'page-count' => 1,
                'total'      => count($response)
            ];
        }

        return $response;
    }

    /**
     * Fetch ALL active products by iterating pages automatically.
     *
     * @return array  Flat array of product records
     */
    public function getAllProducts(): array
    {
        $batchSize = config('idempiere.sync_batch_size', 100);
        $page      = 1;
        $all       = [];

        do {
            $result  = $this->getProducts($page, $batchSize);
            $records = $result['records'] ?? [];
            $all     = array_merge($all, $records);

            Log::debug("[iDempiere] Fetched page {$page}, got " . count($records) . " products.");

            // iDempiere returns page-count in the response header / body
            $pageCount = $result['page-count'] ?? 1;
            $page++;
        } while ($page <= $pageCount && count($records) > 0);

        return $all;
    }

    // -------------------------------------------------------------------------
    // Stock / Inventory
    // -------------------------------------------------------------------------

    /**
     * Fetch on-hand quantity for a specific product from M_StorageOnHand.
     * Returns the sum across all locators in the configured warehouse.
     *
     * @param  int  $productId  M_Product_ID
     * @return float
     */
    public function getStockForProduct(int $productId): float
    {
        try {
            $result = $this->getModel('M_StorageOnHand', [
                '$filter' => "M_Product_ID eq {$productId}",
                '$select' => 'QtyOnHand',
            ]);

            $records = $result['records'] ?? [];

            return array_sum(array_column($records, 'QtyOnHand'));
        } catch (Exception $e) {
            Log::warning("[iDempiere] Could not fetch stock for product {$productId}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Fetch stock levels for multiple products in a single call using OData IN filter.
     * Returns an associative array: [ m_product_id => qty_on_hand ]
     *
     * @param  int[]  $productIds
     * @return array<int, float>
     */
    public function getStockBatch(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $idList = implode(',', $productIds);

        try {
            // Fetch active stock from m_storageonhand for THIS batch of products
            $result = $this->getModel('m_storageonhand', [
                '$filter' => "M_Product_ID in ({$idList})",
                '$select' => 'M_Product_ID,QtyOnHand',
                '$top'    => count($productIds) * 10,
            ]);

            $records = $result['records'] ?? [];
            $stock   = [];

            foreach ($records as $row) {
                // Read M_Product_ID.id from JSON object
                $pid = $row['M_Product_ID']['id'] ?? null;
                $qty = (float) ($row['QtyOnHand'] ?? 0);

                if ($pid !== null) {
                    $stock[(int)$pid] = ($stock[(int)$pid] ?? 0) + $qty;
                }
            }

            return $stock;
        } catch (Exception $e) {
            Log::warning('[iDempiere] getStockBatch notice: ' . $e->getMessage());
            return [];
        }
    }

    // -------------------------------------------------------------------------
    // Prices
    // -------------------------------------------------------------------------

    /**
     * Fetch standard prices for multiple products from M_ProductPrice.
     * Returns an associative array: [ m_product_id => price_std ]
     *
     * @param  int[]  $productIds
     * @return array<int, float>
     */
    public function getPricesBatch(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $versionId = config('idempiere.price_list_version_id', 1000000);
        $idList    = implode(',', $productIds);

        try {
            // Fetch prices for active Price List Version and THIS batch of products
            $result = $this->getModel('m_productprice', [
                '$filter' => "M_Product_ID in ({$idList}) and M_PriceList_Version_ID eq {$versionId}",
                '$select' => 'M_Product_ID,PriceStd,PriceList',
                '$top'    => count($productIds) * 2,
            ]);

            $records = $result['records'] ?? [];
            $prices  = [];

            foreach ($records as $row) {
                // Read M_Product_ID.id from JSON object
                $pid   = $row['M_Product_ID']['id'] ?? null;
                $price = (float) ($row['PriceStd'] ?? $row['PriceList'] ?? 0);

                if ($pid !== null) {
                    $prices[(int)$pid] = $price;
                }
            }

            return $prices;
        } catch (Exception $e) {
            Log::warning('[iDempiere] getPricesBatch notice: ' . $e->getMessage());
            return [];
        }
    }
    // -------------------------------------------------------------------------
    // Categories
    // -------------------------------------------------------------------------

    /**
     * Fetch all product categories from M_Product_Category.
     * Returns an associative array: [ category_id => category_name ]
     *
     * @return array<int, string>
     */
    public function getCategories(): array
    {
        try {
            $result = $this->getModel('M_Product_Category', [
                '$filter' => "IsActive eq 'Y'",
                '$select' => 'M_Product_Category_ID,Name',
                '$top'    => 500,
            ]);

            $categories = [];
            foreach ($result['records'] ?? [] as $row) {
                $id = $row['M_Product_Category_ID']['id'] ?? $row['M_Product_Category_ID'] ?? null;
                if ($id !== null) {
                    $categories[$id] = $row['Name'] ?? 'Unknown';
                }
            }

            return $categories;
        } catch (Exception $e) {
            Log::warning('[iDempiere] getCategories failed: ' . $e->getMessage());
            return [];
        }
    }

    // -------------------------------------------------------------------------
    // Connection Test
    // -------------------------------------------------------------------------

    /**
     * Ping the API and return connection info. Useful for the admin dashboard.
     */
    public function ping(): array
    {
        try {
            $token = $this->getToken();
            return [
                'connected' => true,
                'base_url'  => $this->baseUrl,
                'token_ok'  => !empty($token),
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'base_url'  => $this->baseUrl,
                'error'     => $e->getMessage(),
            ];
        }
    }
    // -------------------------------------------------------------------------
    // Orders
    // -------------------------------------------------------------------------

    /**
     * Create a new Sales Order (C_Order) in iDempiere.
     *
     * @param string $bpartnerValue  The client code/value from the users table.
     * @param string $description    Optional description for the order.
     * @return int|null              Returns the new C_Order_ID, or null on failure.
     */
    
    public function createOrder(string $bpartnerValue, string $description = ''): ?int
    {
        // 1. Lookup the C_BPartner_ID and their configured Document Type
        $bpartnerResult = $this->getModel('c_bpartner', [
            '$filter' => "Value eq '{$bpartnerValue}'",
            '$select' => 'C_BPartner_ID,C_DocTypeTarget_ID',
        ]);

        $bp = $bpartnerResult['records'][0] ?? null;

        if (!$bp) {
            Log::error("[iDempiere] Cannot create order: BPartner with Value '{$bpartnerValue}' not found.");
            return null;
        }

        $bpartnerId = $bp['id'];

        // Use the BPartner's own document type (Facture or BL), fall back to config
        $docTypeId = $bp['C_DocTypeTarget_ID']['id']
            ?? config('idempiere.order_doc_type_id', 1000034);

        Log::info("[iDempiere] Creating order for BPartner '{$bpartnerValue}', DocType: {$docTypeId}");

        // 2. Ensure BPartner has a Ship To location (auto-create if missing)
        $locationId = $this->ensureShipToLocation($bpartnerId);

        $payload = [
            'AD_Client_ID'            => $this->clientId,
            'AD_Org_ID'               => $this->orgId,
            'C_BPartner_ID'           => $bpartnerId,
            'C_DocTypeTarget_ID'      => $docTypeId,
            'M_Warehouse_ID'          => $this->warehouseId,
            'IsSOTrx'                 => true,
            'Description'             => $description,
            'DateOrdered'             => now()->format('Y-m-d\TH:i:s\Z'),
            'DeliveryRule'            => 'A', // "A" = Availability (Auto-generates BL for in-stock parts)
            'DeliveryViaRule'         => 'D', // "D" = Delivery / Livraison
            'InvoiceRule'             => 'D', // "D" = After Delivery (Auto-generates Facture as soon as BL is created)
        ];

        // Pass the location ID explicitly if we found/created one
        if ($locationId) {
            $payload['C_BPartner_Location_ID'] = $locationId;
        }

        Log::info("[iDempiere] Sending Order Payload to ERP: ", $payload);
        session()->push('erp_payloads', [
            'type' => 'C_Order',
            'data' => $payload
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->getToken())
                ->post("{$this->baseUrl}/models/c_order", $payload);

            if (!$response->successful()) {
                Log::error("[iDempiere] Failed to create C_Order: " . $response->body());
                return null;
            }

            return $response->json('id');
        } catch (Exception $e) {
            Log::error("[iDempiere] Exception creating C_Order: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Ensure a BPartner has at least one Ship To location.
     * Returns the location ID, creating a minimal one if needed.
     */
    protected function ensureShipToLocation(int $bpartnerId): ?int
    {
        // Check for an existing Ship To location
        $result = $this->getModel('C_BPartner_Location', [
            '$filter' => "C_BPartner_ID eq {$bpartnerId}",
            '$select' => 'C_BPartner_Location_ID,IsShipTo',
            '$top'    => 5,
        ]);

        $records = $result['records'] ?? [];

        // Return first Ship To location found
        foreach ($records as $loc) {
            if (!empty($loc['IsShipTo'])) {
                return $loc['id'];
            }
        }

        // Return any location if none is explicitly Ship To
        if (!empty($records)) {
            return $records[0]['id'];
        }

        // No location at all — auto-create a minimal one
        Log::info("[iDempiere] BPartner {$bpartnerId} has no location. Auto-creating one.");

        try {
            // First create a C_Location (address record) — Tunisia country ID = 325
            $locResponse = Http::timeout($this->timeout)
                ->withToken($this->getToken())
                ->post("{$this->baseUrl}/models/c_location", [
                    'AD_Client_ID' => $this->clientId,
                    'AD_Org_ID'    => $this->orgId,
                    'C_Country_ID' => 325, // Tunisie
                    'City'         => 'N/A',
                ]);

            if (!$locResponse->successful()) {
                Log::warning("[iDempiere] Could not create C_Location: " . $locResponse->body());
                return null;
            }

            $cLocationId = $locResponse->json('id');

            // Now create C_BPartner_Location linking the BPartner to this address
            $bplResponse = Http::timeout($this->timeout)
                ->withToken($this->getToken())
                ->post("{$this->baseUrl}/models/c_bpartner_location", [
                    'AD_Client_ID'  => $this->clientId,
                    'AD_Org_ID'     => $this->orgId,
                    'C_BPartner_ID' => $bpartnerId,
                    'C_Location_ID' => $cLocationId,
                    'Name'          => 'Livraison',
                    'IsBillTo'      => true,
                    'IsShipTo'      => true,
                    'IsPayFrom'     => true,
                    'IsRemitTo'     => true,
                ]);

            if (!$bplResponse->successful()) {
                Log::warning("[iDempiere] Could not create C_BPartner_Location: " . $bplResponse->body());
                return null;
            }

            return $bplResponse->json('id');
        } catch (Exception $e) {
            Log::warning("[iDempiere] Exception creating location: " . $e->getMessage());
            return null;
        }
    }

    /**
     * M_Product_IDs already on a C_Order, so a retried send never duplicates lines.
     * Throws on API errors (the caller should retry rather than guess).
     *
     * @return int[]
     */
    public function getOrderLineProductIds(int $orderId): array
    {
        $result = $this->getModel('c_orderline', [
            '$filter' => "C_Order_ID eq {$orderId}",
            '$select' => 'M_Product_ID',
        ]);

        return array_values(array_filter(array_map(function ($row) {
            $p = $row['M_Product_ID'] ?? null;
            return (int) (is_array($p) ? ($p['id'] ?? 0) : $p);
        }, $result['records'] ?? [])));
    }

    /**
     * Create a line item for a Sales Order (C_OrderLine).
     *
     * @param int   $orderId    The C_Order_ID returned by createOrder()
     * @param int   $productId  The M_Product_ID (erp_product_id)
     * @param float $qty        Quantity ordered
     * @param float $price      Price of the product
     * @return bool
     */
    public function createOrderLine(int $orderId, int $productId, float $qty, float $price): bool
    {
        $payload = [
            'AD_Client_ID' => $this->clientId,
            'AD_Org_ID'    => $this->orgId,
            'C_Order_ID'   => $orderId,
            'M_Product_ID' => $productId,
            'QtyEntered'   => $qty,
            'QtyOrdered'   => $qty,
            'PriceEntered' => $price,
            'PriceActual'  => $price,
        ];

        Log::info("[iDempiere] Sending OrderLine Payload to ERP (Product $productId): ", $payload);
        session()->push('erp_payloads', [
            'type' => 'C_OrderLine',
            'product_id' => $productId,
            'data' => $payload
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->getToken())
                ->post("{$this->baseUrl}/models/c_orderline", $payload);

            if (!$response->successful()) {
                Log::error("[iDempiere] Failed to create C_OrderLine for Product {$productId}: " . $response->body());
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::error("[iDempiere] Exception creating C_OrderLine: " . $e->getMessage());
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Order Completion & Document Auto-Printing
    // -------------------------------------------------------------------------

    /**
     * Complete the Sales Order in iDempiere (DocAction = 'CO').
     * This triggers iDempiere to automatically generate the BL (M_InOut) and Facture (C_Invoice)!
     *
     * @param int $orderId  The C_Order_ID
     * @return bool
     */
    public function completeOrder(int $orderId): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->getToken())
                ->put("{$this->baseUrl}/windows/ordres-de-vente/{$orderId}", [
                    'doc-action' => 'CO',
                ]);

            if ($response->successful()) {
                Log::info("[iDempiere] Action on Document triggered: Order {$orderId} Completed (CO) successfully. BL and Facture generated!");
                return true;
            }

            Log::error("[iDempiere] Failed to trigger Action on Document for Order {$orderId}: " . $response->body());
            return false;
        } catch (Exception $e) {
            Log::error("[iDempiere] Exception completing order {$orderId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve the automatically generated BL (M_InOut) and Facture (C_Invoice) IDs for this order.
     *
     * @param int $orderId
     * @return array{inout_id: int|null, invoice_id: int|null}
     */
    public function getGeneratedDocuments(int $orderId): array
    {
        $inoutId = null;
        $invoiceId = null;

        try {
            // 1. Find the generated Bon de Livraison (M_InOut)
            $inoutResult = $this->getModel('m_inout', [
                '$filter' => "C_Order_ID eq {$orderId}",
                '$select' => 'M_InOut_ID,DocumentNo',
                '$top'    => 1,
            ]);
            $inoutId = $inoutResult['records'][0]['id'] ?? $inoutResult['records'][0]['M_InOut_ID'] ?? null;

            // 2. Find the generated Facture (C_Invoice)
            $invoiceResult = $this->getModel('c_invoice', [
                '$filter' => "C_Order_ID eq {$orderId}",
                '$select' => 'C_Invoice_ID,DocumentNo',
                '$top'    => 1,
            ]);
            $invoiceId = $invoiceResult['records'][0]['id'] ?? $invoiceResult['records'][0]['C_Invoice_ID'] ?? null;

        } catch (Exception $e) {
            Log::warning("[iDempiere] Could not fetch generated document IDs for order {$orderId}: " . $e->getMessage());
        }

        return [
            'inout_id'   => $inoutId ? (int)$inoutId : null,
            'invoice_id' => $invoiceId ? (int)$invoiceId : null,
        ];
    }

    /**
     * Download the official printable PDF of any document (Facture, BL, or Order) from iDempiere.
     *
     * @param string $model Table name: 'c_order', 'm_inout' (BL), or 'c_invoice' (Facture)
     * @param int    $id    The Record ID
     * @return string|null  Raw PDF binary content (ready to save or send to printer)
     */
    public function getDocumentPdf(string $model, int $id): ?string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->getToken())
                ->get("{$this->baseUrl}/models/{$model}/{$id}/print");

            if ($response->successful()) {
                return $response->body(); // Raw PDF binary
            }

            Log::error("[iDempiere] Failed to download PDF for {$model}/{$id}: " . $response->body());
            return null;
        } catch (Exception $e) {
            Log::error("[iDempiere] Exception fetching PDF for {$model}/{$id}: " . $e->getMessage());
            return null;
        }
    }
}