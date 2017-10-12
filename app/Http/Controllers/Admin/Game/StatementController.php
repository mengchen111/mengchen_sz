<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/12/17
 * Time: 12:17
 */

namespace App\Http\Controllers\Admin\Game;

use App\Services\Game\PlayerService;
use Illuminate\Support\Facades\Cache;

class StatementController
{
    //累计玩家总数
    public function getTotalPlayers()
    {
        return count($this->getAllPlayers());
    }

    //当日新增玩家数
    public function getIncreasedTotalPlayers()
    {
        return $players = $this->getAllPlayers();

    }

    protected function getAllPlayers()
    {
        $cacheKey = config('custom.game_server_cache_players');
        $cacheDuration = config('custom.game_server_cache_duration');

        $players = Cache::remember($cacheKey, $cacheDuration, function () {
            return PlayerService::getAllPlayers();
        });

        return $players;
    }
}