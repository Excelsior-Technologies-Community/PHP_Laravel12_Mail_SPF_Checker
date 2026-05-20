<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SPFCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_spf_page_loads(): void
    {
        $response = $this->get('/mailspf');

        $response->assertStatus(200);

        $response->assertSee('Advanced Mail SPF Checker');
    }
}