<?php

namespace Tests\Feature;

use App\Services\IdempiereService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IdempiereServiceTest extends TestCase
{
    private const BASE = 'http://erp.test/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function fakeAuth(): void
    {
        Http::fake([
            self::BASE . '/auth/tokens' => function (Request $r) {
                return $r->method() === 'POST'
                    ? Http::response(['token' => 'step1'])
                    : Http::response(['token' => 'jwt-final']);
            },
            self::BASE . '/models/*' => Http::response(['records' => [['id' => 1]]]),
        ]);
    }

    public function test_login_is_two_steps_and_sends_session_context(): void
    {
        $this->fakeAuth();

        $token = app(IdempiereService::class)->getToken();

        $this->assertSame('jwt-final', $token);
        Http::assertSent(fn (Request $r) => $r->method() === 'POST'
            && $r['userName'] === 'test-user' && $r['password'] === 'test-pass');
        Http::assertSent(fn (Request $r) => $r->method() === 'PUT'
            && $r->hasHeader('Authorization', 'Bearer step1')
            && isset($r['clientId'], $r['roleId'], $r['organizationId'], $r['warehouseId']));
    }

    public function test_token_is_cached_between_calls(): void
    {
        $this->fakeAuth();
        $erp = app(IdempiereService::class);

        $erp->getModel('m_product');
        $erp->getModel('m_product');

        $logins = Http::recorded(fn (Request $r) => str_ends_with($r->url(), '/auth/tokens') && $r->method() === 'POST');
        $this->assertCount(1, $logins, 'Should log in once and reuse the cached JWT');
    }

    public function test_expired_token_is_refreshed_and_the_call_retried_once(): void
    {
        Cache::put('idempiere_jwt_token', 'expired-jwt', 3600);
        Http::fake([
            self::BASE . '/auth/tokens' => Http::response(['token' => 'fresh-jwt']),
            self::BASE . '/models/m_product*' => function (Request $r) {
                return $r->hasHeader('Authorization', 'Bearer expired-jwt')
                    ? Http::response('Unauthorized', 401)
                    : Http::response(['records' => [['id' => 7]]]);
            },
        ]);

        $result = app(IdempiereService::class)->getModel('m_product');

        $this->assertSame(7, $result['records'][0]['id']);
        $this->assertSame('fresh-jwt', Cache::get('idempiere_jwt_token'));
    }

    public function test_repeated_401_does_not_loop_forever(): void
    {
        Http::fake([
            self::BASE . '/auth/tokens' => Http::response(['token' => 'jwt']),
            self::BASE . '/models/*'    => Http::response('Unauthorized', 401),
        ]);

        $this->expectExceptionMessage('failed (401)');
        app(IdempiereService::class)->getModel('m_product');
    }

    public function test_missing_credentials_fail_clearly_without_any_request(): void
    {
        config(['idempiere.password' => null]);
        Http::fake();

        try {
            app(IdempiereService::class)->getToken();
            $this->fail('Expected a configuration error');
        } catch (\Exception $e) {
            $this->assertStringContainsString('IDEMPIERE_PASSWORD', $e->getMessage());
        }
        Http::assertNothingSent();
    }
}
