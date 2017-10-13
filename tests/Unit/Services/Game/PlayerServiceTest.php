<?php

namespace Tests\Unit\Services\Game;

use Faker\Factory as Faker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\Game\PlayerService;

class PlayerServiceTest extends TestCase
{
    protected $testPlayerId = 10000;

    public function testGetAllPlayers()
    {
        $res = PlayerService::getAllPlayers();
        $this->assertArrayHasKey('uid', $res[0]);
    }

    public function testGetOnePlayerFound()
    {
        $res = PlayerService::getOnePlayer($this->testPlayerId);
        $this->assertEquals($res['uid'], $this->testPlayerId, 'uid not equal');
    }

    /**
     * @expectedException   \App\Exceptions\GameServerException
     */
    public function testGetOnePlayerNotFound()
    {
        $res = PlayerService::getOnePlayer(Faker::create()->name());
    }
}
