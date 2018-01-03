<?php
/**
 * 获取开房次数，游戏局数，游戏时长等数据
 */

namespace App\Services\Game;

use Carbon\Carbon;

class RoomStatementService
{
    protected $roomHistory;     //房间历史
//    protected $roomOpened;      //正在完的房间，暂不计算未结束的房间
//    protected $allRooms;        //全部房间，collection

    public $data = [
        'room_total_count' => 0,                //全天的开房总次数
        'system_opened_room_count' => 0,        //系统开房，直接调用游戏后端接口开的房
        'player_opened_normal_room_count' => 0, //玩家开房，正常耗卡
        'player_opened_invalid_room_count' => 0,//玩家开房，无效房间
        'web_opened_room_count' => 0,           //使用管理后台开房的数量
        'normal_room_opened_players_count' => 0,       //有效房间开房人数
        'invalid_room_opened_players_count' => 0,      //无效房间开房人数
        'game_rounds_total_count' => 0,         //游戏局数
        'game_players_total_count' => 0,             //游戏人数
        'total_player_game_duration' => 0,           //玩家游戏总时长（秒）
        'total_room_game_duration' => 0,             //房间游戏总时长（秒）
    ];

    public function __construct($date, $gameKind = '')
    {
        $roomHistoryApi = config('custom.game_api_room_history');
//        $roomOpenedApi = config('custom.game_api_room_open');
        $this->roomHistory = GameApiService::request('GET', $roomHistoryApi, [
            'date' => $date,
            'game_kind' => $gameKind,   //$gameKind为空('')后端接口会获取所有类型的房间数据
        ]);
//        $this->roomOpened = GameApiService::request('GET', $roomOpenedApi, [
//            'date' => $date,
//            'game_kind' => $gameKind,
//        ]);
//        $this->allRooms = collect($this->roomHistory)->merge(collect($this->roomOpened));
    }

    //计算前端所需要的报表数据
    public function computeData()
    {
        $this->data['room_total_count'] = count($this->roomHistory);
        $this->computeOpenedRoomCountData();   //计算开房相关的数据
        return $this->data;
    }

    protected function computeOpenedRoomCountData()
    {
        $normalRoomOpenedPlayers = collect();  //有效房间开房的玩家id
        $invalidRoomOpenedPlayers = collect(); //无效房间开房的玩家id
        $gamePlayers = collect();   //参与过游戏的玩家

        //正在玩的房，表中没有currency 无法判断无效开房等数据
        foreach ($this->roomHistory as $room) {
            $this->data['game_rounds_total_count'] += $room['cur_round'];
            $gamePlayers = $gamePlayers->merge($this->getGamePlayers($room));  //计算每一行房间历史中的参与的游戏的玩家，合并起来
            $this->data['total_player_game_duration'] += $this->getPlayerGameDuration($room);
            $this->data['total_room_game_duration'] += $this->getRoomGameDuration($room);

            if (0 == $room['agent_uid']) {
                if (0 == $room['creator_uid']) {
                    //系统开房(即直接调用游戏端的php接口开的房)
                    $this->data['system_opened_room_count'] += 1;
                } else {    //0 != $room['creator_uid'] creator_uid不为0，玩家开的房
                    if ($room['currency'] != 0) {
                        //玩家开的房，有效耗卡
                        $this->data['player_opened_normal_room_count'] += 1;
                        $normalRoomOpenedPlayers->push($room['creator_uid']);
                    } else {
                        //玩家开房，无效房间
                        $this->data['player_opened_invalid_room_count'] += 1;
                        $invalidRoomOpenedPlayers->push($room['creator_uid']);
                    }
                }
            } else {    //agent_uid不为0时，此房间为通过web后台开的房
                $this->data['web_opened_room_count'] += 1;
            }
        }

        $this->data['normal_room_opened_players_count'] = $normalRoomOpenedPlayers->unique()->count();
        $this->data['invalid_room_opened_players_count'] = $invalidRoomOpenedPlayers->unique()->count();
        $this->data['game_players_total_count'] = $gamePlayers->unique()->count();  //将所有玩家去重，得到今天玩过游戏的玩家人数
    }

    protected function isInvalidRoom($room)
    {
        if ($room['agent_uid'] == 0 && $room['creator_uid'] != 0 && $room['currency'] == 0) {
            return true;
        }
        return false;
    }

    //获取每个房间的玩家
    public function getGamePlayers($room)
    {
        $uids = [];     //此房间中玩过游戏的玩家id
        for ($i = 1; $i<=4; $i++) {
            $uid = $room['uid_' . $i];
            if (! empty($uid)) {     //如果此字段为空，则说明此位置没有玩家。忽略之，否则添加上去
                array_push($uids, $uid);
            }
        }
        return $uids;
    }

    //获取每个房间的玩家游戏时长
    public function getPlayerGameDuration($room)
    {
        if (0 == $room['player_cnt'] or $this->isInvalidRoom($room)) {
            return 0;
        }

        $startTime = Carbon::parse($room['time'])->timestamp;
        $endTime = Carbon::parse($room['end_time'])->timestamp;

        if ($endTime < $startTime) {
            return 0;       //数据库中有些数据endtime为0，这些房间不计算
        }

        $duration = ($endTime - $startTime) * $room['player_cnt'];

        return $duration;
    }

    //获取每个房间的游戏时长
    public function getRoomGameDuration($room)
    {
        if (0 == $room['player_cnt'] or $this->isInvalidRoom($room)) {
            return 0;
        }

        $startTime = Carbon::parse($room['time'])->timestamp;
        $endTime = Carbon::parse($room['end_time'])->timestamp;

        if ($endTime < $startTime) {
            return 0;       //数据库中有些数据endtime为0，这些房间不计算
        }

        $duration = $endTime - $startTime;
        return $duration;
    }
}