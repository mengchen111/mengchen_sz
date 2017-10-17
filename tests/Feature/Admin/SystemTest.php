<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;

class SystemTest extends TestCase
{
    protected $systemLogApi = '/admin/api/system/log';
    protected $admin;

    public function setUp()
    {
        parent::setUp();

        if (! $this->admin) {
            $this->admin = User::find(1);
        }
    }

    public function testShowLog()
    {
        $response = $this->actingAs($this->admin)
            ->get($this->systemLogApi);
        $response->assertJsonStructure([
            'current_page',
            'data',
        ]);
    }
}
