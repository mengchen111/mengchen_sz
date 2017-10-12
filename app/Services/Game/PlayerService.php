<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/12/17
 * Time: 14:57
 */

namespace App\Services\Game;

use App\Services\Game\GameServer;
use Carbon\Carbon;

class PlayerService
{
    public static function getAllPlayers()
    {
        $gameServer = new GameServer();

        $result = $gameServer->request('GET', config('custom.game_server_api_users'));
        return self::decodeNickname($result['accounts']);
    }

    public static function getOnePlayer($uid)
    {
        $gameServer = new GameServer();

        $result =  $gameServer->request('POST', config('custom.game_server_api_user'), [
            'uid' => $uid,
            'timestamp' => Carbon::now()->timestamp
        ]);

        return self::decodeNickname($result['account']);
    }

    public static function decodeNickname($data)
    {
        //获取一个用户时
        if (isset($data['nickname'])) {
            $data['nickname'] = mb_convert_encoding(base64_decode($data['nickname']), 'UTF-8');;
        } else {
            //获取所有用户时
            foreach ($data as &$user) {
                //必须要将base64解码之后的字符串转码成utf8格式，不然无法序列化成json字符串
                $user['nickname'] = mb_convert_encoding(base64_decode($user['nickname']), 'UTF-8');
            }
        }

        return $data;
    }
}