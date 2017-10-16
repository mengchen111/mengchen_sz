<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/12/17
 * Time: 17:44
 */

namespace App\Services\Game;

use App\Models\TopUpPlayer;
use App\Services\Game\PlayerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class StatementDailyService
{
    protected $allPlayers;
    protected $cardType = 1;

    public function __construct()
    {
        $this->allPlayers = self::getAllPlayers();
    }

    //玩家总数
    public function getTotalPlayersAmount()
    {
        return count($this->allPlayers);
    }

    public function getOnlinePlayersAmount()
    {
        return 0;
        //TODO
    }

    public function getPeakOnlinePlayersAmount()
    {
        return 0;
        //TODO
    }

    //根据日期获取当日活跃玩家数(非今日数据不准确，数据库只保存了最近登录的时间)
    public function getActivePlayersAmount($date)
    {
        return count($this->getActivePlayers($date));
    }

    //根据日期获取当日新增玩家总数
    public function getIncrementalPlayersAmount($date)
    {
        return count($this->getIncrementalPlayers($date));
    }

    /**
     * 根据日期获取查询日的留存数据
     *
     * @param string $searchDate
     * @param string $createDate
     * @return string '2|4|50.00 - 留存玩家数|创建日新增玩家数|百分比(保留两位小数)'
     */
    public function getRemainedData($searchDate, $days)
    {
        $createDate = Carbon::parse($searchDate)->subDay($days)->toDateString();
        $remainedPlayersAmount = count($this->getRemainedPlayers($searchDate, $createDate));
        $yesterdayIncrementalPlayersAmount = $this->getIncrementalPlayersAmount($createDate);

        if ($yesterdayIncrementalPlayersAmount === 0 ) {
            return $remainedPlayersAmount . '|' . $yesterdayIncrementalPlayersAmount . '|'
                . '0.00';
        }
        return "${remainedPlayersAmount}|${yesterdayIncrementalPlayersAmount}|"
            . sprintf('%.2f', $remainedPlayersAmount / $yesterdayIncrementalPlayersAmount * 100); //保留两位小数
    }

    /**
     * 根据日期获取当日耗卡数据
     *
     * @param string $date
     * @return string '280|13|22 - 当日玩家耗卡总数|当日有过耗卡记录的玩家总数|平均耗卡数(向上取整的比值)
     */
    public function getCardConsumedData($date)
    {
        return '0|0|0';
        //TODO 待游戏端给出数据后完成
    }

    /**
     * 根据日期获取当日购卡数据
     *
     * @param string $date
     * @return string '280|13|22 - 当日玩家购卡总数|当日有过购卡记录的玩家总数|平均购卡数(向上取整的比值)
     */
    public function getCardBoughtData($date)
    {
        $players = $this->getCardBoughtPlayers($date);

        //当日玩家购卡的总数
        $cardBoughtAmount = $players->sum('amount');
        //当日有过购卡记录的玩家总数
        $cardBoughtPlayersAmount = $players->groupBy('player')->count();

        if ($cardBoughtPlayersAmount === 0) {
            return "${cardBoughtAmount}|${cardBoughtPlayersAmount}|"
                . '0';
        }

        return "${cardBoughtAmount}|${cardBoughtPlayersAmount}|"
            . ceil($cardBoughtAmount/$cardBoughtPlayersAmount); //向上取整
    }

    //

    /**
     * 获取截止给定日期给玩家充卡的总数，如果不给定日期则查询截止当前给玩家充卡的总数
     *
     * @param string $date '2017-09-22'
     * @return mixed|string
     */
    public function getCardBoughtSum($date = 'today')
    {
        if (Carbon::parse($date)->isToday()) {
            return TopUpPlayer::all()
                ->where('type', $this->cardType)
                ->sum('amount');
        }

        return TopUpPlayer::whereDate('created_at', '<=', $date)
            ->where('type', $this->cardType)
            ->sum('amount');
    }

    /**
     * 获取截止给定日期玩家消耗卡的总数，如果不给定日期则查询截止当前玩家耗卡的总数
     *
     * @param string $date '2017-09-22'
     * @return mixed|string
     */
    public function getCardConsumedSum($date = 'today')
    {
        //TODO 待游戏端给出数据后完成
        if (Carbon::parse($date)->isToday()) {
            //返回当前实时的耗卡总数
            return 0;
        }
        return 0;
    }

    /**
     * 查询给定日期当天的玩家购卡数据(当日给玩家充值的流水记录)
     *
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getCardBoughtPlayers($date)
    {
        return TopUpPlayer::whereDate('created_at', $date)
            ->where('type', $this->cardType)
            ->get();
    }

    /**
     * 根据日期获取留存玩家，比较在查询日(searchDate)登录过且用户注册时间等于(createDate)的玩家
     * 获取非今日数据可能不准确，因为last_time只记录了最后一次登录，未记录登录历史
     *
     * @param string $searchDate
     * @param string $createDate
     *
     * @return array|mixed
     */
    protected function getRemainedPlayers($searchDate, $createDate)
    {
        return array_filter($this->allPlayers, function ($player) use ($searchDate, $createDate) {
            return Carbon::parse($player['create_time'])->toDateString() === $createDate
                && Carbon::parse($player['last_time'])->toDateString() === $searchDate;
        });
    }

    /**
     * 根据日期获取活跃玩家(最后登录时间，非今日数据不准确，因为数据库只保存了最近登录的时间)
     *
     * @param string $date
     *
     * @return array
     */
    protected function getActivePlayers($date)
    {
        return array_filter($this->allPlayers, function ($player) use ($date) {
            $loginDate = Carbon::parse($player['last_time']);
            return $loginDate->toDateString() === $date;
        });
    }

    /**
     * 根据日期获取玩家信息（玩家创建时间）
     *
     * @param string $date
     *
     * @return array
     */
    protected function getIncrementalPlayers($date)
    {
        return array_filter($this->allPlayers, function ($player) use ($date) {
            $createDate = Carbon::parse($player['create_time']);
            return $createDate->toDateString() === $date;
        });
    }

    /**
     * 获取所有玩家信息，缓存3分钟
     *
     * @return array|mixed
     */
    protected static function getAllPlayers()
    {
        $cacheKey = config('custom.game_server_cache_players');
        $cacheDuration = config('custom.game_server_cache_duration');

        $players = Cache::remember($cacheKey, $cacheDuration, function () {
            return PlayerService::getAllPlayers();
        });

        return $players;
    }
}