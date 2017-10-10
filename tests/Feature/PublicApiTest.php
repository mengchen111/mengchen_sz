<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PublicApiTest extends TestCase
{
    public function testInfoApi()
    {
        $admin = User::find(1);
        $response = $this->actingAs($admin)
            ->get('/api/info');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'account',
                'created_at',
                'group',
                'group_id',
                'id',
                'inventorys',
                'name',
                'parent',
                'parent_id',
                'updated_at'
            ]);
    }
}
