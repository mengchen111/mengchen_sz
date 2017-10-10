<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdatePassApiTest extends TestCase
{
    protected $changePassApi = 'admin/api/self/password';

    /**
     * @group admin
     */
    public function testUpdatePass()
    {
        $admin = factory(User::class)->create([
            'group_id' => 1,
            'parent_id' => -1,
            'password' => bcrypt('password'),
        ]);

        //原密码提交错误时
        $response = $this->actingAs($admin)
            ->put($this->changePassApi, [
                'password' => 'aaaaaa',
                'new_password' => '123123',
                'new_password_confirmation' => '123123',
            ]);
        $response->assertJsonStructure(['error']);

        //原密码提交正确时
        $response = $this->actingAs($admin)
            ->put($this->changePassApi, [
                'password' => 'password',
                'new_password' => '123123',
                'new_password_confirmation' => '123123',
            ]);
        $response->assertJsonStructure(['message']);
    }

    /**
     * @group admin
     */
    public function testUpdatePassAsAgent()
    {
        $this->agent = factory(User::class)->create();
        $response = $this->actingAs($this->agent)
            ->put($this->changePassApi);
        $response->assertStatus(403);
    }
}
