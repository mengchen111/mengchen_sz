<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use App\Services\Game\RoomStatementService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoomStatementController extends Controller
{
    public function getRoomStatement(AdminRequest $request)
    {
        $this->validate($request, [
            'date' => 'required|date_format:"Y-m-d"',
            'game_kind' => 'integer',
        ]);

        $date = $request->input('date');
        $gameKind = $request->has('game_kind') ? $request->input('game_kind') : '';
        $data = [];

        $roomStatementService = new RoomStatementService($date, $gameKind);
        $data['room_open_count'] = $roomStatementService->getRoomOpenTotalCount();  //当日开房次数
        $data['room_open_by_card_count'] = $roomStatementService->getRoomOpenByCardCount(); //当日消耗房卡开房次数
        $data['room_open_players_count'] = $roomStatementService->getRoomOpenPlayersCount();    //当日开房人数
        $data['game_rounds_count'] = $roomStatementService->getGameRoundsTotalCount();  //当日游戏局数
        $data['players_count'] = $roomStatementService->getPlayersCount();  //当日游戏人数
        $data['player_avg_game_duration'] = $roomStatementService->getPlayerAvgGameDuration();  //平均游戏时长（小时）
        $data['round_avg_duration'] = $roomStatementService->getRoundAvgDuration(); //单局游戏时长

        return $data;
    }
}
