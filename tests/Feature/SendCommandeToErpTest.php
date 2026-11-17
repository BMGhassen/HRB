<?php

namespace Tests\Feature;

use App\Jobs\SendCommandeToErp;
use App\Models\Commande;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * The iDempiere VM is never contacted: every HTTP call is faked.
 */
class SendCommandeToErpTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'http://erp.test/api/v1';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'idempiere.base_url' => self::BASE,
            'idempiere.username' => 'test-user',
            'idempiere.password' => 'test-pass',
        ]);
    }

    /** Fake iDempiere. $failOrderCreation: return 503 for the first N C_Order POSTs. */
    private function fakeErp(array $existingLineProductIds = [], int $failOrderCreation = 0): void
    {
        $orderPosts = 0;

        Http::fake(function (Request $r) use ($existingLineProductIds, &$orderPosts, $failOrderCreation) {
            $url = $r->url();
            $m   = $r->method();

            return match (true) {
                str_contains($url, '/auth/tokens')                          => Http::response(['token' => 'jwt']),
                str_contains($url, '/models/c_bpartner?')                   => Http::response(['records' => [['id' => 500]]]),
                str_contains($url, '/models/C_BPartner_Location')           => Http::response(['records' => [['id' => 600, 'IsShipTo' => true]]]),
                $m === 'POST' && str_ends_with($url, '/models/c_order')     => ++$orderPosts <= $failOrderCreation
                                                                                ? Http::response('down', 503)
                                                                                : Http::response(['id' => 9001], 201),
                $m === 'GET' && str_contains($url, '/models/c_orderline')   => Http::response(['records' => array_map(
                                                                                   fn ($id) => ['M_Product_ID' => ['id' => $id]], $existingLineProductIds)]),
                $m === 'POST' && str_ends_with($url, '/models/c_orderline') => Http::response(['id' => 1], 201),
                str_contains($url, '/windows/ordres-de-vente/9001')         => Http::response(['id' => 9001]),
                default                                                     => Http::response(['records' => []]),
            };
        });
    }

    private function makeOrder(?string $codeClient = 'C001', ?int $erpProductId = 101): Commande
    {
        $client = User::forceCreate([
            'name' => 'Client', 'email' => uniqid() . '@test.local', 'password' => bcrypt('x'),
            'role' => 'client', 'state' => 'Actif', 'code_client' => $codeClient,
        ]);

        $articleIds = [];
        foreach ([$erpProductId, $erpProductId ? $erpProductId + 1 : null] as $i => $pid) {
            $articleIds[] = DB::table('articles')->insertGetId([
                'ref' => "REF-$i", 'designation' => "Article $i", 'description' => '-',
                'qte_stock' => 50, 'qte_instance' => 0, 'qte_reserve' => 0, 'prix' => 10,
                'erp_product_id' => $pid,
            ]);
        }

        $commande = new Commande();
        $commande->client_id = $client->id;
        $commande->save();

        foreach ($articleIds as $aid) {
            DB::table('ligne_commandes')->insert(['commande_id' => $commande->id, 'article_id' => $aid, 'qte' => 3]);
        }

        return $commande->fresh();
    }

    public function test_validating_queues_the_order_and_decrements_stock(): void
    {
        Queue::fake();
        $commande = $this->makeOrder();

        $this->actingAs($commande->client)
            ->post(route('commande.valider', $commande->id))
            ->assertSessionHas('success');

        $commande->refresh();
        $this->assertSame('Validé', $commande->etat);
        $this->assertSame('pending', $commande->erp_status);
        $this->assertSame(47, (int) DB::table('articles')->where('ref', 'REF-0')->value('qte_stock'));
        Queue::assertPushed(SendCommandeToErp::class, fn ($job) => $job->commandeId === $commande->id);
    }

    public function test_job_creates_lines_and_completes_the_order(): void
    {
        $this->fakeErp();
        $commande = $this->makeOrder();

        SendCommandeToErp::dispatchSync($commande->id);

        $commande->refresh();
        $this->assertSame('sent', $commande->erp_status);
        $this->assertSame(9001, (int) $commande->erp_order_id);
        $this->assertCount(2, Http::recorded(
            fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/models/c_orderline')
        ));
        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/windows/ordres-de-vente/9001') && $r['doc-action'] === 'CO');
    }

    public function test_retry_does_not_duplicate_order_or_existing_lines(): void
    {
        $this->fakeErp(existingLineProductIds: [101]);
        $commande = $this->makeOrder();
        $commande->forceFill(['erp_order_id' => 9001, 'erp_status' => 'pending'])->save();

        SendCommandeToErp::dispatchSync($commande->id);

        Http::assertNotSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/models/c_order'));
        $linePosts = Http::recorded(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/models/c_orderline'));
        $this->assertCount(1, $linePosts);
        $this->assertSame(102, $linePosts->first()[0]['M_Product_ID']);
        $this->assertSame('sent', $commande->fresh()->erp_status);
    }

    public function test_erp_outage_throws_so_the_queue_retries(): void
    {
        $this->fakeErp(failOrderCreation: 1);
        $commande = $this->makeOrder();

        try {
            SendCommandeToErp::dispatchSync($commande->id);
            $this->fail('Expected the job to throw so the queue retries it.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Création de la commande ERP', $e->getMessage());
        }

        $this->assertNull($commande->fresh()->erp_order_id);
    }

    public function test_missing_erp_product_id_fails_without_calling_erp(): void
    {
        $this->fakeErp();
        $commande = $this->makeOrder(erpProductId: null);

        SendCommandeToErp::dispatchSync($commande->id);

        $commande->refresh();
        $this->assertSame('failed', $commande->erp_status);
        $this->assertStringContainsString('Articles sans ID ERP', $commande->erp_error);
        Http::assertNothingSent();
    }

    public function test_admin_can_resend_a_failed_order(): void
    {
        Queue::fake();
        $commande = $this->makeOrder();
        $commande->forceFill(['etat' => 'Validé', 'erp_status' => 'failed', 'erp_error' => 'boom'])->save();
        $admin = User::forceCreate(['name' => 'A', 'email' => 'a@test.local', 'password' => bcrypt('x'), 'role' => 'admin']);

        $this->actingAs($admin)->post(route('commandes.renvoyer', $commande->id))->assertSessionHas('success');

        $this->assertSame('pending', $commande->fresh()->erp_status);
        Queue::assertPushed(SendCommandeToErp::class);
    }
}
