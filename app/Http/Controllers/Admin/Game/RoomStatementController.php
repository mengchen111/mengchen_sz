<?php

namespace App\Http\Controllers\Admin\Game;

use App\Exceptions\CustomException;
use App\Exceptions\RoomStatementServiceException;
use App\Http\Requests\AdminRequest;
use App\Traits\MajiangTypeMap;
use App\Services\Game\RoomStatementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\OperationLogs;

class RoomStatementController extends Controller
{
    use MajiangTypeMap;

    public function getRoomStatement(AdminRequest $request)
    {
        $this->validate($request, [
            'date' => 'required|date_format:"Y-m-d"',
            //'game_kind' => 'integer',
        ]);

        $date = $request->input('date');
        $gameKind = $request->has('game_kind') ? $request->input('game_kind') : '';

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看开房数据报表', $request->header('User-Agent'), json_encode($request->all()));

        //今天之前的数据缓存一份到redis，今天的数据实时计算获取
        try {
            //需要判断是过去的日期，不然如果提交的是未来的日期，那么将来此日期的数据不会再更新
            if (Carbon::parse($date)->isPast() && !Carbon::parse($date)->isToday()) {
                $cacheKey = config('custom.cache_room_statement') . ':' . $date . ':' . $gameKind;
                $data = Cache::rememberForever($cacheKey, function () use ($date, $gameKind) {
                    $roomStatementService = new RoomStatementService($date, $gameKind);
                    return $roomStatementService->computeData();
                });
            } else {
                $roomStatementService = new RoomStatementService($date, $gameKind);
                $data = $roomStatementService->computeData();
            }
        } catch (RoomStatementServiceException $exception) {
            throw new CustomException($exception->getMessage());
        }

        return $data;
    }

    //导出开房数据报表为excel
    public function exportRoomStatement(AdminRequest $request)
    {
        $this->validate($request, [
            'date' => 'required|date_format:"Y-m-d"',
            //'game_kind' => 'integer',
        ]);

        //获取所有房间类型的数据报表
        $roomStatement['全部'] = $this->getRoomStatement($request);
        foreach ($this->maJiangTypes as $typeId => $typeName) {
            $request->merge(['game_kind' => $typeId]);  //每种游戏类型都请求一次，获取数据
            $roomStatement[$typeName] = $this->getRoomStatement($request);
        }

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '导出开房数据报表', $request->header('User-Agent'), json_encode($request->all()));

        $fileName = '开房数据_' . $request->input('date');
        $data = $this->buildExcelData($roomStatement);
        Excel::create($fileName, function ($excel) use ($data) {
            foreach ($data as $gameType => $statement) {
                $excel->sheet($gameType, function ($sheet) use ($statement) {
                    foreach ($statement as $k => $v) {
                        $sheet->appendRow([$k, $v]);
                    }
                });
            }
        })->export('xls');
    }

    protected function buildExcelData($roomStatement)
    {
        $result = [];
        foreach ($roomStatement as $gameType => $statement) {
            $data = [];
            $data['开房总次数'] = $statement['room_total_count'];
            $data['房卡开房次数'] = $statement['player_opened_normal_room_count'];
            $data['后台开房次数'] = $statement['web_opened_room_count'];
            $data['无效开房次数'] = $statement['player_opened_invalid_room_count'];
            $data['开房人数'] = $statement['normal_room_opened_players_count'];
            $data['平均开房次数'] = $statement['normal_room_opened_players_count'] != 0
                ? sprintf('%.2f', $statement['player_opened_normal_room_count'] / $statement['normal_room_opened_players_count'])
                : 0;
            $data['游戏局数'] = $statement['game_rounds_total_count'];
            $data['游戏人数'] = $statement['game_players_total_count'];
            $data['平均局数'] = $statement['game_players_total_count'] != 0
                ? sprintf('%.2f', $statement['game_rounds_total_count'] / $statement['game_players_total_count'])
                : 0;
            $data['平均游戏时长(小时)'] = $statement['game_players_total_count'] != 0
                ? sprintf('%.2f', $statement['total_player_game_duration'] / $statement['game_players_total_count'] / 3600)
                : 0;
            $data['单局游戏时长(秒)'] = $statement['game_rounds_total_count'] != 0
                ? sprintf('%.2f', $statement['total_room_game_duration'] / $statement['game_rounds_total_count'])
                : 0;
            $data['平均组局时间(分)'] = $statement['room_played_total_count'] != 0
                ? sprintf('%.2f', $statement['total_room_zuju_duration'] / $statement['room_played_total_count'] / 60)
                : 0;

            $result[$gameType] = $data;
        }

        return $result;
    }
}
