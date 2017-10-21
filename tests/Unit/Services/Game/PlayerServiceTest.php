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
        $this->assertArrayHasKey('id', $res[0]);
    }

    public function testSearchPlayersFound()
    {
        $res = PlayerService::searchPlayers($this->testPlayerId);
        $this->assertEquals($res[0]['id'], $this->testPlayerId, 'user id not equal');
    }

    public function testGetOnePlayerNotFound()
    {
        $res = PlayerService::searchPlayers(Faker::create()->randomNumber(9));
        $this->assertTrue(empty($res));
    }
}
