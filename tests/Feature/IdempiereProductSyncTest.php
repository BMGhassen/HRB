<?php

namespace Tests\Feature;

use App\Services\IdempiereProductSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IdempiereProductSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Fake iDempiere. Each argument is a list of records, or an int HTTP status to fail with.
     * $storagePages: list of pages for the paginated stock-only sync.
     */
    private function fakeErp(array $products = [], array|int $prices = [], array|int $stocks = [], array $storagePages = []): void
    {
        Http::fake(function (Request $r) use ($products, $prices, $stocks, $storagePages) {
            $path = parse_url($r->url(), PHP_URL_PATH);
            parse_str((string) parse_url($r->url(), PHP_URL_QUERY), $q);
            $reply = fn ($data) => is_int($data) ? Http::response('error', $data) : Http::response(['records' => $data]);

            if (str_ends_with($path, '/auth/tokens')) {
                return Http::response(['token' => 'jwt']);
            }
            if (str_ends_with($path, '/models/m_product')) {
                $skip = (int) ($q['$skip'] ?? 0);
                return $reply(array_slice($products, $skip, (int) ($q['$top'] ?? 100)));
            }
            if (str_ends_with($path, '/models/m_productprice')) {
                return $reply($prices);
            }
            if (str_ends_with($path, '/models/m_storageonhand')) {
                if (isset($q['$filter'])) {          // per-batch stock in the catalog sync
                    return $reply($stocks);
                }
                $page = intdiv((int) ($q['$skip'] ?? 0), 100);   // paginated stock-only sync
                return $reply($storagePages[$page] ?? []);
            }
            return Http::response('unexpected ' . $path, 500);
        });
    }

    private function product(int $id, ?string $ref, string $name = 'Part'): array
    {
        return ['id' => $id, 'Value' => $ref, 'Name' => $name, 'Description' => '',
                'M_Product_Category_ID' => ['identifier' => 'Suspension']];
    }

    private function row(int $pid, float $qty, string $ref = ''): array
    {
        return ['M_Product_ID' => ['id' => $pid, 'identifier' => $ref ? "{$ref}_X" : ''], 'QtyOnHand' => $qty];
    }

    private function article(string $ref, ?int $erpId, float $prix, float $stock, string $sel = '0'): int
    {
        return DB::table('articles')->insertGetId([
            'ref' => $ref, 'designation' => $ref, 'description' => '-', 'erp_product_id' => $erpId,
            'qte_stock' => $stock, 'qte_instance' => 3, 'qte_reserve' => 0, 'prix' => $prix, 'sel' => $sel,
        ]);
    }

    private function sync(bool $full = true): array
    {
        return app(IdempiereProductSync::class)->sync(dryRun: false, limit: 0, fullSync: $full);
    }

    // ── Catalog sync ──────────────────────────────────────────────────────────

    public function test_creates_articles_with_price_summed_stock_and_category(): void
    {
        $this->fakeErp(
            products: [$this->product(101, 'REF-A', 'Ressort'), $this->product(102, null)],
            prices:   [['M_Product_ID' => ['id' => 101], 'PriceStd' => 206.47]],
            stocks:   [$this->row(101, 3), $this->row(101, 2)],
        );

        $report = $this->sync();

        $a = DB::table('articles')->where('ref', 'REF-A')->first();
        $this->assertSame(101, (int) $a->erp_product_id);
        $this->assertEqualsWithDelta(206.47, $a->prix, 0.001);
        $this->assertSame(5, (int) $a->qte_stock, 'stock from several locators is summed');
        $this->assertSame('Suspension', $a->erp_category);
        $this->assertSame(1, $report['total']);
        $this->assertSame(1, $report['skipped'], 'product without a Value (SKU) is skipped');
        $this->assertSame(0, $report['errors']);
    }

    public function test_uses_list_price_when_standard_price_is_zero(): void
    {
        // Real iDempiere data: ~11.6k products have PriceStd = 0 and the price only in PriceList
        $this->fakeErp(
            products: [$this->product(101, 'REF-A'), $this->product(102, 'REF-B'), $this->product(103, 'REF-C')],
            prices:   [
                ['M_Product_ID' => ['id' => 101], 'PriceStd' => 0, 'PriceList' => 33.85],
                ['M_Product_ID' => ['id' => 102], 'PriceStd' => 12.5, 'PriceList' => 12.5],
                ['M_Product_ID' => ['id' => 103], 'PriceStd' => 0, 'PriceList' => 0],
            ],
        );

        $this->sync();

        $this->assertEqualsWithDelta(33.85, DB::table('articles')->where('ref', 'REF-A')->value('prix'), 0.001);
        $this->assertEqualsWithDelta(12.5, DB::table('articles')->where('ref', 'REF-B')->value('prix'), 0.001);
        $this->assertEqualsWithDelta(0, DB::table('articles')->where('ref', 'REF-C')->value('prix'), 0.001);
    }

    public function test_updating_keeps_local_only_fields(): void
    {
        $id = $this->article('REF-A', 101, 10, 1, sel: '1');
        $this->fakeErp(
            products: [$this->product(101, 'REF-A', 'New name')],
            prices:   [['M_Product_ID' => ['id' => 101], 'PriceStd' => 12]],
            stocks:   [$this->row(101, 9)],
        );

        $this->sync();

        $a = DB::table('articles')->find($id);
        $this->assertSame(1, DB::table('articles')->count(), 'no duplicate row');
        $this->assertSame('New name', $a->designation);
        $this->assertEqualsWithDelta(12, $a->prix, 0.001);
        $this->assertSame(9, (int) $a->qte_stock);
        $this->assertSame('1', $a->sel, 'Vedette flag is local and must survive a sync');
        $this->assertSame(3, (int) $a->qte_instance);
    }

    public function test_reads_every_page_of_the_catalog(): void
    {
        $products = array_map(fn ($i) => $this->product(1000 + $i, "REF-$i"), range(1, 150));
        $this->fakeErp(products: $products);

        $report = $this->sync();

        $this->assertSame(150, $report['total']);
        $this->assertSame(150, DB::table('articles')->count());
    }

    public function test_second_run_is_a_delta_sync_and_full_sync_is_not(): void
    {
        $this->fakeErp(products: [$this->product(101, 'REF-A')]);

        $this->sync(full: false);
        $this->sync(full: false);
        $this->sync(full: true);

        $productCalls = Http::recorded(fn (Request $r) => str_contains($r->url(), '/models/m_product?'))
            ->map(fn ($pair) => urldecode($pair[0]->url()))->values();
        $this->assertStringNotContainsString('Updated gt', $productCalls[0], 'first run has no timestamp yet');
        $this->assertStringContainsString('Updated gt', $productCalls[1]);
        $this->assertStringNotContainsString('Updated gt', $productCalls[2]);
    }

    public function test_failed_price_request_does_not_zero_prices_and_stock(): void
    {
        $id = $this->article('REF-A', 101, 206.47, 6);
        $this->fakeErp(products: [$this->product(101, 'REF-A')], prices: 500, stocks: [$this->row(101, 6)]);

        $report = $this->sync();

        $a = DB::table('articles')->find($id);
        $this->assertEqualsWithDelta(206.47, $a->prix, 0.001, 'price must not be overwritten with 0');
        $this->assertSame(6, (int) $a->qte_stock);
        $this->assertSame(1, $report['errors']);
        $this->assertNull(Cache::get('erp_last_sync_timestamp'), 'a failed sync must not advance the delta timestamp');
    }

    public function test_failed_stock_request_does_not_zero_stock(): void
    {
        $id = $this->article('REF-A', 101, 10, 6);
        $this->fakeErp(products: [$this->product(101, 'REF-A')],
                       prices: [['M_Product_ID' => ['id' => 101], 'PriceStd' => 10]], stocks: 503);

        $report = $this->sync();

        $this->assertSame(6, (int) DB::table('articles')->find($id)->qte_stock);
        $this->assertSame(1, $report['errors']);
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->fakeErp(products: [$this->product(101, 'REF-A')]);

        $report = app(IdempiereProductSync::class)->sync(dryRun: true, fullSync: true);

        $this->assertSame(1, $report['total']);
        $this->assertSame(0, DB::table('articles')->count());
    }

    // ── Stock-only sync (every 10 minutes) ───────────────────────────────────

    public function test_stock_sync_updates_by_erp_id_and_zeroes_articles_gone_from_storage(): void
    {
        $a = $this->article('REF-A', 101, 10, 1);
        $b = $this->article('REF-B', 102, 10, 7);   // no longer in iDempiere storage
        $this->fakeErp(storagePages: [[$this->row(101, 4, 'REF-A'), $this->row(101, 1, 'REF-A')]]);

        $result = app(IdempiereProductSync::class)->syncStockOnly();

        $this->assertTrue($result['success']);
        $this->assertSame(5, (int) DB::table('articles')->find($a)->qte_stock);
        $this->assertSame(0, (int) DB::table('articles')->find($b)->qte_stock);
    }

    public function test_stock_sync_reads_all_pages(): void
    {
        $page1 = array_map(fn ($i) => $this->row(1000 + $i, 1), range(1, 100));
        $last  = $this->article('REF-LAST', 2000, 10, 0);
        $this->fakeErp(storagePages: [$page1, [$this->row(2000, 8)]]);

        app(IdempiereProductSync::class)->syncStockOnly();

        $this->assertSame(8, (int) DB::table('articles')->find($last)->qte_stock);
    }

    public function test_stock_sync_reads_many_pages_in_parallel_batches(): void
    {
        // 25 full pages + a short last one: more than 3 parallel batches of 8
        $pages = [];
        foreach (range(0, 24) as $p) {
            $pages[] = array_map(fn ($i) => $this->row(10000 + $p * 100 + $i, 1), range(0, 99));
        }
        $pages[] = [$this->row(2000, 8)];
        $last = $this->article('REF-LAST', 2000, 10, 0);
        $first = $this->article('REF-FIRST', 10000, 10, 0);
        $this->fakeErp(storagePages: $pages);

        $result = app(IdempiereProductSync::class)->syncStockOnly();

        $this->assertTrue($result['success']);
        $this->assertSame(2501, $result['storage_items_found']);
        $this->assertSame(8, (int) DB::table('articles')->find($last)->qte_stock);
        $this->assertSame(1, (int) DB::table('articles')->find($first)->qte_stock);
    }

    public function test_stock_sync_uses_sku_only_for_articles_without_erp_id(): void
    {
        $byId  = $this->article('SAME-REF', 101, 10, 0);   // matched by ID
        $bySku = $this->article('SKU-ONLY', null, 10, 0);  // no ERP id: matched by SKU
        $this->fakeErp(storagePages: [[$this->row(101, 3, 'SAME-REF'), $this->row(555, 7, 'SKU-ONLY')]]);

        $result = app(IdempiereProductSync::class)->syncStockOnly();

        $this->assertSame(2, $result['articles_updated']);
        $this->assertSame(3, (int) DB::table('articles')->find($byId)->qte_stock);
        $this->assertSame(7, (int) DB::table('articles')->find($bySku)->qte_stock);
    }

    public function test_stock_sync_renews_an_expired_token(): void
    {
        $a = $this->article('REF-A', 101, 10, 0);
        Cache::put('idempiere_jwt_token', 'expired', 3600);
        Http::fake(function (Request $r) {
            if (str_contains($r->url(), '/auth/tokens')) {
                return Http::response(['token' => 'fresh']);
            }
            if (!$r->hasHeader('Authorization', 'Bearer fresh')) {
                return Http::response('Unauthorized', 401);
            }
            parse_str((string) parse_url($r->url(), PHP_URL_QUERY), $q);
            return Http::response(['records' => ((int) ($q['$skip'] ?? 0)) === 0 ? [$this->row(101, 4)] : []]);
        });

        $result = app(IdempiereProductSync::class)->syncStockOnly();

        $this->assertTrue($result['success'], $result['error'] ?? '');
        $this->assertSame(4, (int) DB::table('articles')->find($a)->qte_stock);
    }

    public function test_catalog_sync_renews_a_token_that_expires_mid_sync(): void
    {
        $this->fakeErp(products: [$this->product(101, 'REF-A')], prices: [['M_Product_ID' => ['id' => 101], 'PriceStd' => 5]]);
        $erp = app(\App\Services\IdempiereService::class);
        $erp->getToken();
        // The JWT the sync started with is no longer accepted for prices/stock
        Http::fake(function (Request $r) {
            $path = parse_url($r->url(), PHP_URL_PATH);
            if (str_ends_with($path, '/auth/tokens')) {
                return Http::response(['token' => 'fresh']);
            }
            if (str_ends_with($path, '/models/m_product')) {
                return Http::response(['records' => [$this->product(101, 'REF-A')]]);
            }
            if (!$r->hasHeader('Authorization', 'Bearer fresh')) {
                return Http::response('Unauthorized', 401);
            }
            return str_ends_with($path, '/models/m_productprice')
                ? Http::response(['records' => [['M_Product_ID' => ['id' => 101], 'PriceStd' => 5]]])
                : Http::response(['records' => [$this->row(101, 2)]]);
        });

        $report = $this->sync();

        $this->assertSame(0, $report['errors'], implode(' | ', $report['error_log']));
        $this->assertEqualsWithDelta(5, DB::table('articles')->where('ref', 'REF-A')->value('prix'), 0.001);
    }

    public function test_stock_sync_failing_midway_changes_nothing(): void
    {
        $a = $this->article('REF-A', 101, 10, 5);   // on page 1
        $b = $this->article('REF-B', 2000, 10, 9);  // on page 2, which fails
        $page1 = array_merge([$this->row(101, 4)], array_map(fn ($i) => $this->row(1000 + $i, 1), range(1, 99)));
        Http::fake(function (Request $r) use ($page1) {
            if (str_contains($r->url(), '/auth/tokens')) {
                return Http::response(['token' => 'jwt']);
            }
            parse_str((string) parse_url($r->url(), PHP_URL_QUERY), $q);
            return ((int) ($q['$skip'] ?? 0)) === 0
                ? Http::response(['records' => $page1])
                : Http::response('gateway timeout', 504);
        });

        $result = app(IdempiereProductSync::class)->syncStockOnly();

        $this->assertFalse($result['success']);
        $this->assertSame(9, (int) DB::table('articles')->find($b)->qte_stock, 'must not be zeroed from a partial read');
        $this->assertSame(5, (int) DB::table('articles')->find($a)->qte_stock, 'partial data is not applied at all');
    }
}
