<?php

namespace App\Http\Controllers\Agent;

use App\Http\Requests\AgentRequest;
use App\Models\CommunityList;
use App\Traits\GameTypeMap;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use App\Services\Game\GameApiService;
use App\Services\Game\PlayerService;

class CommunityRoomController extends Controller
{
    use GameTypeMap;

    /**
     * 获取牌艺馆正在玩的房间信息
     *
     * @SWG\Get(
     *     path="/agent/api/community/room/{community_id}",
     *     description="获取牌艺馆正在玩的房间信息",
     *     operationId="agent.community.room.get",
     *     tags={"community"},
     *
     *     @SWG\Parameter(
     *         name="community_id",
     *         description="牌艺馆id",
     *         in="path",
     *         required=true,
     *         type="integer",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回牌艺馆正在玩的房间信息",
     *         @SWG\Property(
     *             type="array",
     *             @SWG\Items(
     *                 allOf={
     *                     @SWG\Schema(ref="#/definitions/ServerRoom4"),
     *                 },
     *                 @SWG\Property(
     *                     property="players",
     *                     type="array",
     *                     @SWG\Items(
     *                         allOf={
     *                             @SWG\Schema(ref="#/definitions/GamePlayerCommunityRoom"),
     *                         },
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function getCommunityOpenRoom(AgentRequest $request, $communityId)
    {
        $api = config('custom.game_api_community_room_open');
        $params['community_id'] = $communityId;
        $params['is_full'] = 2; //显示所有（包括满员和非满员）
        $openRooms = GameApiService::request('GET', $api, $params);

        $openRooms = $this->formatRoomData($openRooms);
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看社区房间', $request->header('User-Agent'), json_encode($request->all()));

        return $openRooms;
    }
    protected function formatRoomData($rooms)
    {
        foreach ($rooms as &$room){
            unset($room['options']);
            $room['kind'] = $this->gameTypes[$room['kind']];
        }
        return $rooms;
    }

//    protected function formatRoomData($rooms)
//    {
//        $allPlayers = collect(PlayerService::getAllPlayers());
//        foreach ($rooms as &$room) {
//            unset($room['options_jstr']);
//            $room['rtype'] = $this->gameTypes[$room['rtype']];
//            $room['players'] = [];
//            for ($i = 1; $i <= 4; $i++) {
//                $tmp = [];
//                if ($room['uid_' . $i] != 0) {   //为0表示此座位没人玩，不查询之
//                    $player = $allPlayers->where('id', $room['uid_' . $i])->first();
//                    $tmp['nickname'] = $player['nickname'];
//                    $tmp['headimg'] = $player['headimg'];
//                }
//                $tmp['uid'] = $room['uid_' . $i];
//                $tmp['score'] = $room['score_' . $i];
//                array_push($room['players'], $tmp);
//            }
//        }
//
//        return $rooms;
//    }
}
