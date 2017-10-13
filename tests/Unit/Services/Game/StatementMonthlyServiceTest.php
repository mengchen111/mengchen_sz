<?php

namespace Tests\Unit\Services\Game;

use App\Models\TopUpPlayer;
use App\Services\Game\StatementMonthlyService;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StatementMonthlyServiceTest extends TestCase
{
    protected $cardTypeId = 1;
    protected $playerId = 10000;
    protected $amount = 100;    //充卡数量
    protected $count = 4;       //充卡次数
    protected $today;

    public function setUp()
    {
        parent::setUp();

        $this->today = Carbon::now();

        factory(TopUpPlayer::class, $this->count)->create([    //创建房卡充值记录(相同玩家)
            'amount' => $this->amount,
            'player' => $this->playerId,
            'type' => $this->cardTypeId,
            'created_at' => $this->today->toDateString(),
        ]);
        factory(TopUpPlayer::class, 1)->create([                //创建一个非房卡充值记录(相同玩家)
            'amount' => $this->amount,
            'player' => $this->playerId,
            'type' => $this->cardTypeId + 1,
            'created_at' => $this->today->toDateString(),
        ]);
    }

    public function testGetMonthlyCardBoughtSum()
    {
        $res = StatementMonthlyService::getMonthlyCardBoughtSum($this->today->format('Y-m'));
        $this->assertEquals($res, $this->amount * $this->count, 'getMonthlyCardBoughtSum返回的购卡总量错误');

        //查询下个月的购卡总量，应该返回0
        $res = StatementMonthlyService::getMonthlyCardBoughtSum($this->today->addMonth(1)->format('Y-m'));
        $this->assertEquals($res, 0, 'getMonthlyCardBoughtSum返回的购卡总量非0');
    }

    public function testGetMonthlyCardBoughtPlayersSum()
    {
        $res = StatementMonthlyService::getMonthlyCardBoughtPlayersSum($this->today->format('Y-m'));
        $this->assertEquals($res, 1, 'getMonthlyCardBoughtPlayersSum返回的玩家数量错误');

        //为另一个玩家充值非房卡
        factory(TopUpPlayer::class, $this->count)->create([    //创建房卡充值记录(相同玩家)
            'amount' => $this->amount,
            'player' => $this->playerId + 1,
            'type' => $this->cardTypeId + 1,
            'created_at' => $this->today->toDateString(),
        ]);

        $res = StatementMonthlyService::getMonthlyCardBoughtPlayersSum($this->today->format('Y-m'));
        $this->assertEquals($res, 1, '为新道具类型充值之后getMonthlyCardBoughtPlayersSum返回的玩家数量错误');

        //为另一个玩家充值房卡
        factory(TopUpPlayer::class, $this->count)->create([    //创建房卡充值记录(相同玩家)
            'amount' => $this->amount,
            'player' => $this->playerId + 1,
            'type' => $this->cardTypeId,
            'created_at' => $this->today->toDateString(),
        ]);

        $res = StatementMonthlyService::getMonthlyCardBoughtPlayersSum($this->today->format('Y-m'));
        $this->assertEquals($res, 2, '为另一个玩家充值之后getMonthlyCardBoughtPlayersSum返回的玩家数量错误');

        //查询下个月的有过购卡的玩家总数，应该返回0
        $res = StatementMonthlyService::getMonthlyCardBoughtPlayersSum($this->today->addMonth(1)->format('Y-m'));
        $this->assertEquals($res, 0, 'getMonthlyCardBoughtSum返回的玩家数量非0');
    }
}