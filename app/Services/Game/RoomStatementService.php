<?php
/**
 * 获取开房次数，游戏局数，游戏时长等数据
 */

namespace App\Services\Game;

use Carbon\Carbon;

class RoomStatementService
{
    protected $roomHistory;     //房间历史

    public function __construct($date, $gameKind = '')
    {
        $roomHistoryApi = config('custom.game_api_room_history');
        $this->roomHistory = GameApiService::request('GET', $roomHistoryApi, [
            'date' => $date,
            'game_kind' => $gameKind,   //$gameKind为空('')后端接口会获取所有类型的房间数据
        ]);
    }

    /**
     * 获取指定日期全天的开房次数（统计server_room_history_4的总条目数(日期约束)）
     * 此开房记录包括后台和房卡开房, 约束条件为"开房时间为$date"的时间(即使房间结束时间是"明天")
     */
    public function getRoomOpenTotalCount()
    {
        return count($this->roomHistory);
    }

    //获取指定日期消耗房卡开房的次数(玩家自己开房的agent_uid字段为0)
    public function getRoomOpenByCardCount()
    {
        return collect($this->roomHistory)->where('agent_uid', 0)->count();
    }

    //当日开房人数
    public function getPlayersOpenedRoomCount()
    {

    }

    //当日游戏局数(统计)
    public function getGameRoundsTotalCount()
    {
        return collect($this->roomHistory)->sum('cur_round');
    }

    //当日游戏人数(统计server_room_history_4中的uid_x,剔除重复)
    public function getPlayedGamePlayersCount()
    {
        $uids = [];     //有过游戏记录的玩家ID
        foreach ($this->roomHistory as $room) {
            for ($i = 1; $i++; $i <= 4) {
                $uid = $room['uid_' . $i];
                if (! $uid) {
                    array_push($uids, $uid);
                }
            }
        }
        return collect($uids)->unique()->count();
    }

    //当日游戏总时长
    public function getGameDuration()
    {
        $duration = 0;
        foreach ($this->roomHistory as $room) {
            $startTime = Carbon::parse($room['time'])->timestamp;
            $endTime = Carbon::parse($room['end_time'])->timestamp;
            $duration += $endTime - $startTime;
        }
        return $duration;
    }
}