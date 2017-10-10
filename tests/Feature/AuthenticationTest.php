<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthenticationTest extends TestCase
{
    protected $agent;
    protected $admin;
    protected $faker;
    protected $defaultUserPass = 'password';
    protected $defaultAdminPass = 'admin123#';
    protected $adminHome = '/admin/home';
    protected $agentHome = '/agent/home';
    protected $loginPageUri = '/login';
    protected $loginActionUri = '/login';
    protected $logOutActionUri = '/logout';

    public function setUp()
    {
        parent::setUp();
        $this->agent = factory(User::class)->create();
        $this->admin = User::find(1);
        $this->faker = \Faker\Factory::create();
    }

    //测试登录页面的展示
    public function testLoginForm()
    {
        $response = $this->get($this->loginPageUri);
        $response->assertStatus(200);
    }

    //登录过的管理员访问登录页面应该跳转到管理员的home页
    public function testLoginPageAsAdmin()
    {
        $response = $this->actingAs($this->admin)
            ->get($this->loginPageUri);
        $response->assertRedirect($this->adminHome);
    }

    //登录过的代理商访问登录页面应该跳转到代理商的home页
    public function testLoginPageAsAgent()
    {
        $response = $this->actingAs($this->agent)
            ->get($this->loginPageUri);
        $response->assertRedirect($this->agentHome);
    }

    public function testLoginActionAsAgentWithRightPass()
    {
        $response = $this->post($this->loginActionUri, [
            'account' => $this->agent->account,
            'password' => $this->defaultUserPass,
        ], [
            'Accept' => 'application/json',
        ]);
        $response->assertRedirect($this->agentHome);
    }

    public function testLoginActionAsAgentWithWrongPass()
    {
        $response = $this->post($this->loginActionUri, [
            'account' => $this->agent->account,
            'password' => $this->faker->password,
        ], [
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(422);
    }

    public function testLoginActionAsAdminWithRightPass()
    {
        $response = $this->post($this->loginActionUri, [
            'account' => $this->admin->account,
            'password' => $this->defaultAdminPass,
        ], [
            'Accept' => 'application/json',
        ]);
        $response->assertRedirect($this->adminHome);
    }

    public function testLoginActionAsAdminWithWrongPass()
    {
        $response = $this->post($this->loginActionUri, [
            'account' => $this->admin->account,
            'password' => $this->faker->password,
        ], [
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(422);
    }

    public function testLogoutAction()
    {
        $response = $this->actingAs($this->agent)
            ->post($this->logOutActionUri);
        $response->assertRedirect('/');
    }
}
