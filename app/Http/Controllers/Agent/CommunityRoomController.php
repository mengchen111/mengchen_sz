<?php

namespace App\Http\Controllers\Agent;

use App\Http\Requests\AgentRequest;
use App\Models\CommunityList;
use App\Services\Game\MajiangTypeMap;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use App\Services\Game\GameApiService;
use App\Services\Game\PlayerService;

class CommunityRoomController extends Controller
{
    use MajiangTypeMap;

    public function getCommunityRoom(AgentRequest $request, $communityId)
    {
        $api = config('custom.game_api_room_open');
        $openRooms = GameApiService::request('GET', $api);
        $communityOpenRooms = collect($openRooms)
            ->where('community_id', $communityId)
            ->toArray();
        $result = $this->formatRoomData($communityOpenRooms);
        krsort($result);    //最新的房间放最上

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看社区房间', $request->header('User-Agent'), json_encode($request->all()));

        return $result;
    }

    protected function formatRoomData($rooms)
    {
        $allPlayers = collect(PlayerService::getAllPlayers());
        foreach ($rooms as &$room) {
            unset($room['options_jstr']);
            $room['rtype'] = $this->maJiangTypes[$room['rtype']];
            $room['players'] = [];
            for ($i = 1; $i <= 4; $i++) {
                $tmp = [];
                if ($room['uid_' . $i] != 0) {   //为0表示此座位没人玩，不查询之
                    $player = $allPlayers->where('id', $room['uid_' . $i])->first();
                    $tmp['nickname'] = $player['nickname'];
                    $tmp['headimg'] = $player['headimg'];
                }
                $tmp['uid'] = $room['uid_' . $i];
                $tmp['score'] = $room['score_' . $i];
                array_push($room['players'], $tmp);
            }
        }

        return $rooms;
    }
}
