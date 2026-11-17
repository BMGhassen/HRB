<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Any HTTP call a test did not fake throws instead of reaching the real iDempiere VM
        Http::preventStrayRequests();

        // Pages render without compiled assets (public/build is not in git, e.g. on CI)
        $this->withoutVite();
    }
}
