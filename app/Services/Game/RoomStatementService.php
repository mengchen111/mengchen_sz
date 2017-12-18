<?php
/**
 * 获取开房次数，游戏局数，游戏时长等数据
 */

namespace App\Services\Game;

use Carbon\Carbon;

class RoomStatementService
{
    protected $roomHistory;     //房间历史
    protected $roomOpened;        //正在完的房间

    public function __construct($date, $gameKind = '')
    {
        $roomHistoryApi = config('custom.game_api_room_history');
        $roomOpenedApi = config('custom.game_api_room_open');
        $this->roomHistory = GameApiService::request('GET', $roomHistoryApi, [
            'date' => $date,
            'game_kind' => $gameKind,   //$gameKind为空('')后端接口会获取所有类型的房间数据
        ]);
        $this->roomOpened = GameApiService::request('GET', $roomOpenedApi, [
            'date' => $date,
            'game_kind' => $gameKind,
        ]);
    }

    /**
     * 获取指定日期全天的开房次数（统计server_rooms_history_4和server_rooms_4的总条目数(日期约束)）
     * 此开房记录包括后台和房卡开房, 约束条件为"开房时间为$date"的时间(即使房间结束时间是"明天")
     */
    public function getRoomOpenTotalCount()
    {
        return count($this->roomHistory) + count($this->roomOpened);
    }

    //获取指定日期消耗房卡开房的次数(玩家自己开房的agent_uid字段为0)
    public function getRoomOpenByCardCount()
    {
        return collect($this->roomHistory)
                ->merge(collect($this->roomOpened))
                ->where('agent_uid', 0)
                ->count();
    }

    //当日开房人数(统计server_rooms_history_4的当日总条目数(日期和uid1约束，非代理商后台开房约束))
    public function getRoomOpenPlayersCount()
    {
        return collect($this->roomHistory)
            ->merge(collect($this->roomOpened))
            ->where('agent_uid', 0)
            ->groupby('uid_1')  //uid_1为房主
            ->count();
    }

    //当日游戏局数(统计)
    public function getGameRoundsTotalCount()
    {
        return collect($this->roomHistory)->sum('cur_round');
    }

    //当日游戏人数(统计server_rooms_history_4中的uid_x,剔除重复)
    public function getPlayersCount()
    {
        $uids = [];     //有过游戏记录的玩家ID
        foreach ($this->roomHistory as $room) {
            for ($i = 1; $i<=4; $i++) {
                $uid = $room['uid_' . $i];
                if (! empty($uid)) {
                    array_push($uids, $uid);
                }
            }
        }
        return collect($uids)->unique()->count();
    }

    //当日玩家平均游戏时长（server_rooms_history_4表的当日房间时长 * player_cnt / 游戏人数）
    public function getPlayerAvgGameDuration()
    {
        $duration = 0;
        foreach ($this->roomHistory as $room) {
            $startTime = Carbon::parse($room['time'])->timestamp;
            $endTime = Carbon::parse($room['end_time'])->timestamp;
            $duration += ($endTime - $startTime) * $room['player_cnt'];
        }
        $playersCount = $this->getPlayersCount();
        if ($playersCount == 0) {
            return 0;
        }
        return sprintf('%.2f', ($duration / $playersCount) / 3600);  //返回小时单位(保留两位小数)
    }

    //当日当局游戏时长（server_rooms_history_4表中的当日房间时长 / 当日游戏局数）
    public function getRoundAvgDuration()
    {
        $duration = 0;
        foreach ($this->roomHistory as $room) {
            $startTime = Carbon::parse($room['time'])->timestamp;
            $endTime = Carbon::parse($room['end_time'])->timestamp;
            $duration += $endTime - $startTime;
        }
        $roundCount = $this->getGameRoundsTotalCount();
        if ($roundCount == 0) {
            return 0;
        }
        return ceil($duration / $roundCount);     //返回秒
    }
}