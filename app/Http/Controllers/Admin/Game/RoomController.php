<?php

namespace App\Http\Controllers\Admin\Game;

use App\Models\OperationLogs;
use App\Models\User;
use App\Services\Game\GameApiService;
use App\Services\Game\MaJiangOptionsMap;
use App\Services\Game\MajiangTypeMap;
use App\Services\Game\PlayerService;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RoomController extends Controller
{
    use MajiangTypeMap;
    use MaJiangOptionsMap;

    protected $per_page = 15;
    protected $page = 1;

    protected $availableRoomType = [    //目前可创建的几种房间类型
          4, 6, 7,
    ];

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->page = $request->page ?: $this->page;
    }

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
        $roomsHistory = GameApiService::request('GET', $api);
        $result = $this->formatRoomData($roomsHistory);
        krsort($result);    //最新的房间放最上

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看房间历史', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($result, $this->per_page, $this->page);
    }

    //给玩家添加昵称和headimg
    protected function formatRoomData($rooms)
    {
        foreach ($rooms as &$room) {
            $room['total_round'] = $room['options_jstr'][2];

            $agent = User::find($room['agent_uid']);
            $room['agent_account'] = !empty($agent) ? $agent->account : "Unknown[{$room['agent_uid']}]";
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

    public function create(AdminRequest $request)
    {
        $this->validateCreateForm($request);
        $formData = $this->buildCreateFormData($request);
        $api = config('custom.game_api_room_create');

        $res = GameApiService::request('POST', $api, $formData);

        return [
            'message' => '房间：' . $res['room_id'] . ' 创建成功',
        ];
    }

    protected function buildCreateFormData($request)
    {
        $formData = [];
        $roomId = array_search($request->room, $this->maJiangTypes);
        $formData[1] = $roomId;  //房间类型
        $formData[2] = $request->rounds;    //局数
        $formData[3] = 4;   //人数
        $formData[17] = $request->ma_pai;   //抓马数

        //转换玩法数据
        $availableWanfa = $this->getWanfa($this->maJiangtypeOptions[$roomId]);
        foreach ($availableWanfa as $wanfa) {
            $wanfaId = array_search($wanfa, $this->maJiangOptionsMap['wanfa']);
            $formData[$wanfaId] = in_array($wanfa, $request->wanfa) ? 1 : 0;    //如果传递过来的玩法在可选玩法中，那么置1，否者置0
        }

        //转换鬼牌数据
        foreach ($request->gui_pai as $guiPaiName => $guiPaiValue) {
            foreach ($this->maJiangOptionsMap['gui_pai'] as $key => $value) {
                if ($value['name'] === $guiPaiName) {
                    $formData[$key] = $guiPaiValue;
                }
            }
        }

        $formData['creator'] = Auth::id();

        return $formData;
    }

    //获取可创建的房间类型
    public function getRoomType(AdminRequest $request)
    {
        $rooms = [];    //可创建的房间列表
        $roomType = []; //每种房间可用的选项列表
        foreach ($this->availableRoomType as $typeId) {
            $maJiangTypeSortedOptions = [];     //将可用的玩法选项归类（wanfa，gui_pai, ma_pai）
            $maJiangTypeName = $this->maJiangTypes[$typeId];
            $maJiangTypeOption = $this->maJiangtypeOptions[$typeId];
            $maJiangTypeSortedOptions['wanfa'] = $this->getWanFa($maJiangTypeOption);  //获取可用玩法列表
            $maJiangTypeSortedOptions['gui_pai'] = $this->getGuiPai($maJiangTypeOption);    //获取可用鬼牌玩法
            $maJiangTypeSortedOptions['ma_pai'] = $this->getMaPai($maJiangTypeOption);   //获取马牌玩法

            $roomType[$maJiangTypeName] = $maJiangTypeSortedOptions;
            array_push($rooms, $maJiangTypeName);
        }

        return [
            'rooms' => $rooms,
            'room_type' => $roomType,
        ];
    }

    protected function validateCreateForm($request)
    {
        $this->validate($request, [
            'room' => 'required',
            'rounds' => 'required|integer',
            'wanfa' => 'present',
            'gui_pai' => 'required',
            'ma_pai' => 'required|integer',
        ]);
    }

    protected function getWanfa($options)
    {
        $wanFas = [];   //可选玩法
        array_walk($options, function ($option) use (&$wanFas) {
            if (array_key_exists($option, $this->maJiangOptionsMap['wanfa'])) {
                array_push($wanFas, $this->maJiangOptionsMap['wanfa'][$option]);
            }
        });
        return $wanFas;
    }

    protected function getGuiPai($options)
    {
        $guiPais = [];   //可选鬼牌
        array_walk($options, function ($option) use (&$guiPais) {
            if (array_key_exists($option, $this->maJiangOptionsMap['gui_pai'])) {
                $guiPais[$this->maJiangOptionsMap['gui_pai'][$option]['name']]
                    = $this->maJiangOptionsMap['gui_pai'][$option]['options'];
            }
        });
        return $guiPais;
    }

    protected function getMaPai($options)
    {
        $maPai = [];   //可选玩法
        array_walk($options, function ($option) use (&$maPai) {
            if (array_key_exists($option, $this->maJiangOptionsMap['ma_pai'])) {
                $maPai[$this->maJiangOptionsMap['ma_pai'][$option]] = 0;
                //array_push($maPai, $this->maJiangOptionsMap['ma_pai'][$option]);
            }
        });
        return $maPai;
    }
}
