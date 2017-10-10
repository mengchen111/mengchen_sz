<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SiteRootTest extends TestCase
{
    public function testRootRedirect()
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
