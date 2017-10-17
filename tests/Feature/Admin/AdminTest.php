<?php

namespace Tests\Feature\Admin;

use Faker\Factory as Faker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;

class AdminTest extends TestCase
{
    protected $updatePassApi = '/admin/api/self/password';
    protected $admin;
    protected $adminPass = 'admin123#';

    public function setUp()
    {
        parent::setUp();

        if (! $this->admin) {
            $this->admin = User::find(1);
        }
    }

    public function testUpdatePassWithWrongPass()
    {
        $response = $this->actingAs($this->admin)
            ->put($this->updatePassApi, [
                'password' => Faker::create()->name,
                'new_password' => '123123',
                'new_password_confirmation' => '123123',
            ]);
        $response->assertJsonStructure(['error']);
    }

    public function testUpdatePassWithRightPass()
    {
        $response = $this->actingAs($this->admin)
            ->put($this->updatePassApi, [
                'password' => $this->adminPass,
                'new_password' => '123123',
                'new_password_confirmation' => '123123',
            ]);
        $response->assertJsonStructure(['message']);
    }
}
