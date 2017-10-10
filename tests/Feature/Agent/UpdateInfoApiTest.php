<?php

namespace Tests\Feature\Agent;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateInfoApiTest extends TestCase
{
    protected $agent;
    protected $updateInfoApi = '/agent/api/self/info';

    public function setUp()
    {
        parent::setUp();
        $this->agent = factory(User::class)->create();
    }

    /**
     * @group agent
     */
    public function testUpdateInfo()
    {
        $response = $this->actingAs($this->agent)
            ->put($this->updateInfoApi, [
                'name' => 'test',
                'email' => 'test@test.com',
                'phone' => '18888888888',
            ]);
        $response->assertJsonStructure(['message']);
    }
}
