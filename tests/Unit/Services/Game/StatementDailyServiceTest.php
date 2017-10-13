<?php

namespace Tests\Unit\Services\Game;

use App\Services\Game\StatementDailyService;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\TopUpPlayer;

class StatementDailyServiceTest extends TestCase
{
    protected $statementDailyService;
    protected $date;
    protected $today;
    protected $cardTypeId = 1;
    protected $playerId = 10000;
    protected $amount = 100;    //充卡数量
    protected $count = 4;       //充卡次数

    public function setUp()
    {
        parent::setUp();

        if (! $this->statementDailyService) {
            $this->statementDailyService = new StatementDailyService();
            $this->date = Carbon::now()->toDateString();
        }
    }

    public function testGetTotalPlayers()
    {
        $res = $this->statementDailyService->getTotalPlayers();
        $this->assertGreaterThan(0, $res);
    }

    public function testGetActivePlayersAmount()
    {
        $res = $this->statementDailyService->getActivePlayersAmount($this->date);
        $this->assertGreaterThanOrEqual(0, $res);
    }

    public function testGetIncrementalPlayersAmount()
    {
        $res = $this->statementDailyService->getIncrementalPlayersAmount($this->date);
        $this->assertGreaterThanOrEqual(0, $res);
    }

    public function testGetRemainedData()
    {
        $res = $this->statementDailyService->getRemainedData($this->date, 1);
        $this->assertRegExp('/\d\|\d\|/', $res);  //返回数据格式 2|4|50.00
    }

    public function testGetCardConsumedData()
    {
        //TODO 待完成
    }

    public function testGetCardBoughtData()
    {
        //查询的当天没有玩家充值时
        $res = $this->statementDailyService->getCardBoughtData($this->date);
        $this->assertRegExp("/0|0|/", $res);

        $this->topUpCard4Player();

        //给玩家充值之后数据变化
        $total = $this->amount * $this->count;
        $res = $this->statementDailyService->getCardBoughtData($this->date);
        $this->assertRegExp("/${total}|1|${total}/", $res); //返回数据400|1|400
    }

    public function topUpCard4Player()
    {
        //给玩家充值
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

    public function testGetCardBoughtSum()
    {
        $resBeforeTopUp = $this->statementDailyService->getCardBoughtSum();
        $this->assertGreaterThanOrEqual(0, $resBeforeTopUp);

        $this->topUpCard4Player();

        //给玩家充值之后，总量变化
        $resAfterTopUp = $this->statementDailyService->getCardBoughtSum();
        $this->assertEquals($resBeforeTopUp + $this->amount * $this->count, $resAfterTopUp);
    }
}
