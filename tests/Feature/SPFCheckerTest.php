<?php

namespace Tests\Feature;

use Tests\TestCase;

class SPFCheckerTest extends TestCase
{
    public function test_spf_page_loads()
    {
        $response = $this->get('/mailspf');
        $response->assertStatus(200);
        $response->assertSee('Mail SPF Checker');
    }
}