<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/12/17
 * Time: 14:57
 */

namespace App\Services\Game;

use App\Models\StatisticOnlinePlayer;
use App\Services\Game\GameApiService;
use BadMethodCallException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PlayerService
{
    public static function getAllPlayers()
    {
        $cacheKey = config('custom.game_server_cache_players');
        $cacheDuration = config('custom.game_server_cache_duration');

        return Cache::remember($cacheKey, $cacheDuration, function () {
            return GameApiService::request('GET', self::playersApi());
        });
    }

    public static function searchPlayers($uid)
    {
        return GameApiService::request('POST', self::playersApi(), [
            'uid' => $uid,
        ]);
    }

    public static function getNickName($uid)
    {
        $allPlayers = collect(self::getAllPlayers());
        $player = $allPlayers->where('id', $uid)->first();

        if (empty($player)) {
            return null;
        }

        return $player['nickname'];
    }

    public static function getOnlinePlayersAmount()
    {
        return GameApiService::request('GET', self::playersOnlineAmountApi());
    }

    public static function getOnlinePlayersPeak($date)
    {
        return GameApiService::request('GET', self::playersOnlinePeakApi(), [
            'date' => $date,
        ]);
    }

    public static function getInGamePlayersCount()
    {
        return GameApiService::request('GET', self::playersInGameApi());
    }

    public static function getInGamePlayersPeak($date)
    {
        return GameApiService::request('GET', self::playersInGamePeakApi(), [
            'date' => $date,
        ]);
    }

    //获取平均在线人数
    public static function getAverageOnlinePlayersCount($date)
    {
        $date = Carbon::parse($date)->toDateString();
        $players = StatisticOnlinePlayer::whereDate('created_at', $date)->get();
        if ($players->isEmpty()) {
            return 0;
        }
        return ceil($players->avg('online_count'));
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
            case 'playersOnlineAmountApi':
                return config('custom.game_api_players_online_amount');
                break;
            case 'playersOnlinePeakApi':
                return config('custom.game_api_players_online_peak');
                break;
            case 'playersInGameApi':
                return config('custom.game_api_players_in-game');
                break;
            case 'playersInGamePeakApi':
                return config('custom.game_api_players_in-game_peak');
                break;
            default:
                throw new BadMethodCallException('Call to undefined method ' . self::class . "::${name}()");
        }
    }
}