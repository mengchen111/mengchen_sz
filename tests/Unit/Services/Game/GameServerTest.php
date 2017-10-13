<?php

namespace Tests\Unit\Services;

use App\Services\Game\GameServer;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GameServerTest extends TestCase
{
    public function testGetRightData()
    {
        $body = base64_encode(file_get_contents(__DIR__ . '/GameServerTestGetResponse.json'));
        $mock = new MockHandler([
            new Response(200, [], $body),   //当游戏服正常返回数据
        ]);
        $handler = HandlerStack::create($mock);
        $gameServer = new GameServer($handler);

        $res = $gameServer->request('GET', 'uri');
        $this->assertArrayHasKey('accounts', $res);
    }

    /**
     * @expectedException   \App\Exceptions\GameServerException
     * @expectedExceptionMessageRegExp  /调用接口成功，但是游戏服返回的结果错误/
     */
    public function testGetEmptyDataException()
    {
        $mock = new MockHandler([
            new Response(200, [], ''),      //当游戏服正常，但是返回的数据为空
        ]);
        $handler = HandlerStack::create($mock);
        $gameServer = new GameServer($handler);

        $gameServer->request('GET', 'uri');
    }

    /**
     * @expectedException   \App\Exceptions\GameServerException
     * @expectedExceptionMessageRegExp /调用游戏服接口失败/
     */
    public function testGetDataTimeout()
    {
        $mock = new MockHandler([
            new ConnectException('Connection timed out', new Request('GET', 'test')),   //连接超时时
        ]);
        $handler = HandlerStack::create($mock);
        $gameServer = new GameServer($handler);

        $gameServer->request('GET', 'uri');
    }

    public function testPostDataSuccess()
    {
        $body = base64_encode(file_get_contents(__DIR__ . '/GameServerTestPostResponse.json'));
        $mock = new MockHandler([
            new Response(200, [], $body),   //当游戏服正常返回数据
        ]);
        $handler = HandlerStack::create($mock);
        $gameServer = new GameServer($handler);

        $res = $gameServer->request('POST', 'uri');
        $this->assertArrayHasKey('code', $res);
        $this->assertEquals(0, $res['code']);
    }

    /**
     * @expectedException \App\Exceptions\GameServerException
     * @expectedExceptionMessageRegExp /调用接口成功，但是游戏服返回的结果错误/
     */
    public function testPostDataAbnormalResponse()
    {
        $mock = new MockHandler([
            new Response(200, [], ''),   //当游戏服正常返回数据
        ]);
        $handler = HandlerStack::create($mock);
        $gameServer = new GameServer($handler);

        $gameServer->request('POST', 'uri');
    }

    /**
     * @expectedException \App\Exceptions\GameServerException
     * @expectedExceptionMessageRegExp /调用游戏服接口失败/
     */
    public function testPostDataTimeout()
    {
        $mock = new MockHandler([
            new ConnectException('Connection timed out', new Request('GET', 'test')),   //连接超时时
        ]);
        $handler = HandlerStack::create($mock);
        $gameServer = new GameServer($handler);

        $gameServer->request('POST', 'uri');
    }
}
