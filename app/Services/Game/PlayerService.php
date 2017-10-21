<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/12/17
 * Time: 14:57
 */

namespace App\Services\Game;

use App\Services\Game\GameApiService;
use BadMethodCallException;
use Carbon\Carbon;

class PlayerService
{
    public static function getAllPlayers()
    {
        return GameApiService::request('GET', self::playersApi());
    }

    public static function searchPlayers($uid)
    {
        return GameApiService::request('POST', self::playersApi(), [
            'uid' => $uid,
        ]);
    }

    //老接口传回来的昵称是base64编码的
//    protected static function decodeNickname($data)
//    {
//        //获取一个用户时
//        if (isset($data['nickname'])) {
//            $data['nickname'] = mb_convert_encoding(base64_decode($data['nickname']), 'UTF-8');;
//        } else {
//            //获取所有用户时
//            foreach ($data as &$user) {
//                //必须要将base64解码之后的字符串转码成utf8格式，不然无法序列化成json字符串
//                $user['nickname'] = mb_convert_encoding(base64_decode($user['nickname']), 'UTF-8');
//            }
//        }
//
//        return $data;
//    }

    public static function __callStatic($name, $arguments)
    {
        switch ($name) {
            case 'playersApi':
                return config('custom.game_api_players');
                break;
            default:
                throw new BadMethodCallException('Call to undefined method ' . self::class . "::${name}()");
        }
    }
}