<?php

namespace Tests\Feature\Admin\Game;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;

class StatementSummaryTest extends TestCase
{
    protected $admin;
    protected $rightResStructure = ['peak_online_players']; //正常返回的数据包含此key
    protected $errorResStructure = ['error'];
    protected $statementSummaryApi = '/admin/api/statement/summary';
    protected $statementRealTimeApi = '/admin/api/statement/real-time';

    public function setUp()
    {
        parent::setUp();
        if (! $this->admin) {
            $this->admin = factory(User::class)->create([
                'group_id' => 1,
                'parent_id' => -1,
                'password' => bcrypt('password'),
            ]);
        }
    }

    public function testShow()
    {
        //获取实时数据
        $res = $this->actingAs($this->admin)
            ->get($this->statementSummaryApi);
        $res->assertJsonStructure($this->rightResStructure);

        //获取昨日数据
        $res = $this->actingAs($this->admin)
            ->get($this->statementSummaryApi . '?date=1970-01-01');
        $res->assertJsonStructure($this->errorResStructure);
    }

    public function testShowRealTimeData()
    {
        $res = $this->actingAs($this->admin)
            ->get($this->statementRealTimeApi);
        $res->assertJsonStructure([
            'total_players_amount',
            'online_players_amount'
        ]);
    }
}
