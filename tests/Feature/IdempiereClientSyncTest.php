<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Services\IdempiereClientSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IdempiereClientSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function fakeErp(array $partners, array $locations = []): void
    {
        Http::fake(function (Request $r) use ($partners, $locations) {
            $path = parse_url($r->url(), PHP_URL_PATH);
            parse_str((string) parse_url($r->url(), PHP_URL_QUERY), $q);
            $page = fn (array $rows) => Http::response(['records' => array_slice($rows, (int) ($q['$skip'] ?? 0), (int) ($q['$top'] ?? 100))]);

            return match (true) {
                str_ends_with($path, '/auth/tokens')                 => Http::response(['token' => 'jwt']),
                str_ends_with($path, '/models/C_BPartner')           => $page($partners),
                str_ends_with($path, '/models/C_BPartner_Location')  => $page($locations),
                default                                              => Http::response('unexpected', 500),
            };
        });
    }

    private function bp(int $id, ?string $code, string $name, bool $active = true, ?string $tax = 'MF123'): array
    {
        return ['id' => $id, 'Value' => $code, 'Name' => $name, 'TaxID' => $tax, 'IsActive' => $active];
    }

    private function loc(int $bpId, bool $shipTo, string $address, string $city, ?string $phone = null): array
    {
        return ['C_BPartner_ID' => ['id' => $bpId], 'IsShipTo' => $shipTo, 'Name' => 'Loc', 'Phone' => $phone,
                'C_Location_ID' => ['Address1' => $address, 'City' => $city, 'RegionName' => 'Sfax']];
    }

    public function test_creates_and_updates_clients_from_customers(): void
    {
        Client::create(['code' => '1000', 'rs' => 'Old name']);
        $this->fakeErp(
            [$this->bp(1, '1000', 'KHAYATI LASAAD'), $this->bp(2, '1001', 'NEW GARAGE')],
            [$this->loc(2, true, 'Route de Tunis km 4', 'Sfax', '+216 74 123 456')],
        );

        $report = app(IdempiereClientSync::class)->sync();

        $this->assertSame(['created' => 1, 'updated' => 1, 'errors' => 0, 'skip' => 0], $report);
        $this->assertSame('KHAYATI LASAAD', Client::where('code', '1000')->value('rs'));
        $new = Client::where('code', '1001')->first();
        $this->assertSame('NEW GARAGE', $new->rs);
        $this->assertSame('Route de Tunis km 4, Sfax', $new->Adresse);
        $this->assertSame('Sfax', $new->Region);
        $this->assertSame(21674123456, (int) $new->tel, 'phone keeps digits only');
    }

    public function test_skips_inactive_customers_and_ones_without_code(): void
    {
        $this->fakeErp([$this->bp(1, '1000', 'Inactive', active: false), $this->bp(2, null, 'No code')]);

        $report = app(IdempiereClientSync::class)->sync();

        $this->assertSame(2, $report['skip']);
        $this->assertSame(0, Client::count());
    }

    public function test_ship_to_address_wins_over_other_addresses(): void
    {
        $this->fakeErp(
            [$this->bp(1, '1000', 'Client')],
            [$this->loc(1, false, 'Billing street', 'Tunis'), $this->loc(1, true, 'Warehouse road', 'Sfax'), $this->loc(1, false, 'Other', 'Sousse')],
        );

        app(IdempiereClientSync::class)->sync();

        $this->assertSame('Warehouse road, Sfax', Client::where('code', '1000')->value('Adresse'));
    }

    public function test_reads_every_page_of_customers(): void
    {
        $partners = array_map(fn ($i) => $this->bp($i, (string) (2000 + $i), "Client $i"), range(1, 230));
        $this->fakeErp($partners);

        $report = app(IdempiereClientSync::class)->sync();

        $this->assertSame(230, $report['created']);
        $this->assertSame(230, Client::count());
    }
}
