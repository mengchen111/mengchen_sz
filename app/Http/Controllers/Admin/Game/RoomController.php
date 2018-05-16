<?php

namespace App\Http\Controllers\Admin\Game;

use App\Models\OperationLogs;
use App\Models\User;
use App\Services\Game\GameApiService;
use App\Services\Game\GameOptionsService;
use App\Traits\GameRulesMap;
use App\Traits\GameTypeMap;
use App\Services\Game\PlayerService;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RoomController extends Controller
{
    use GameTypeMap;
    use GameRulesMap;

    protected $per_page = 15;
    protected $page = 1;

    protected $availableRoomType = [    //目前可创建的几种房间类型
          1, 2, 4, 5, 6, 7, 8, 9, 10, 11
    ];

    public function showOpenRoom(AdminRequest $request)
    {
        $api = config('custom.game_api_room_open');
        $openRooms = GameApiService::request('GET', $api);
        $result = $this->formatRoomData($openRooms);
        krsort($result);    //最新的房间放最上

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看正在玩的房间', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($result, $this->per_page, $this->page);
    }

    public function showRoomHistory(AdminRequest $request)
    {
        $api = config('custom.game_api_room_history');

        //房间历史缓存3分钟（数据格式化时间太耗时）
        $cacheKey = config('custom.game_server_cache_room_history');
        $cacheDuration = config('custom.game_server_cache_duration');
        $result = Cache::remember($cacheKey, $cacheDuration, function () use ($api) {
            $roomsHistory = collect(GameApiService::request('GET', $api))
                ->where('agent_uid', '!=', 0)   //房间历史，只获取后台创建的房间（不然数据量很大）
                ->toArray();
            return $this->formatRoomData($roomsHistory);
        });

        krsort($result);    //最新的房间放最上

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看房间历史', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($result, $this->per_page, $this->page);
    }

    //给玩家添加昵称和headimg
    protected function formatRoomData($rooms)
    {
        foreach ($rooms as &$room) {
            if ($room['agent_uid'] == 0) {
                $room['agent_account'] = 0;
            } else {
                $agent = User::find($room['agent_uid']);
                $room['agent_account'] = !empty($agent) ? $agent->account : '管理员(不存在): ' . $room['agent_uid'];
            }

            $room['players'] = [];
            for ($i = 1; $i <= 4; $i++) {
                $tmp = [];
                if ($room['uid_' . $i] != 0) {   //为0表示此座位没人玩，不查询之
                    $player = collect(PlayerService::getAllPlayers())
                        ->where('id', $room['uid_' . $i])
                        ->first();
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

    public function create(AdminRequest $request, GameOptionsService $gameOptionsService)
    {
        $this->validateCreateForm($request);
        $gameType = $request->room;
        $formData = $gameOptionsService->convertCategoricalOption2GameOption($request->all(), $gameType);
        $formData['creator'] = Auth::id();
        $api = config('custom.game_api_room_create');
        $res = GameApiService::request('POST', $api, $formData);

        return [
            'message' => '房间：' . $res['room_id'] . ' 创建成功',
        ];
    }

    public function getRoomType(AdminRequest $request, GameOptionsService $gameOptionsService)
    {
        $rooms = array_intersect_key($this->gameTypes, array_fill_keys($this->availableRoomType, ''));        //可创建的玩法列表
        $roomOptions = [];  //每种房间可用的选项
        foreach ($this->availableRoomType as $typeId) {
            $typeName = $this->gameTypes[$typeId];
            $roomOptions[$typeName] = $gameOptionsService->getCategoricalOption($typeId);
        }
        return [
            'rooms' => $rooms,
            'room_types' => $roomOptions,
        ];
    }

    protected function validateCreateForm($request)
    {
        $this->validate($request, [
            'room' => 'required|in:'.implode(',', array_keys($this->gameTypes)), //玩法类型
            'players' => 'required|integer',    //玩家数量
            'rounds' => 'required|integer',     //局数
            'wanfa' => 'nullable',              //玩法选项
            'gui_pai' => 'integer',             //鬼牌
            'hui_pai' => 'ineger',              //花牌
            'ma_pai' => 'integer',              //马牌
        ]);
    }

    //获取游戏类型映射关系，公共接口
    public function getRoomTypeMap(Request $request)
    {
        return $this->gameTypes;
    }
}
