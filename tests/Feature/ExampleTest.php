<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_loads(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_client_area_requires_login(): void
    {
        $this->get('/client/dashboard')->assertRedirect('/login');
    }
}
