<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Services\Game\PlayerService;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function searchPlayer(Request $request)
    {
        $this->validate($request, [
            'player_id' => 'required|integer',
            'nickname' => 'string',
        ]);

        $playerId = $request->has('player_id') ? $request->input('player_id') : '';
        $nickname = $request->has('nickname') ? $request->input('nickname') : '';

        $player = PlayerService::searchPlayers($playerId, $nickname);

        if (empty($player)) {
            throw new CustomException('玩家不存在');
        }

        //因为后端是模糊搜索，所以需要比较下具体的id是否与用户输入相等
        if ($player[0]['id'] != $playerId) {
            throw new CustomException('玩家不存在');
        }

        return $player[0];
    }
}
