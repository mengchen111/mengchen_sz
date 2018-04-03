<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\CommunityList;
use App\Services\Game\PlayerService;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function searchPlayer(Request $request)
    {
        $this->validate($request, [
            'player_id' => 'required|integer',
            'community_id' => 'required|integer',
            'nickname' => 'string',
        ]);

        $playerId = $request->has('player_id') ? $request->input('player_id') : '';
        $nickname = $request->has('nickname') ? $request->input('nickname') : '';

        $result = PlayerService::searchPlayers($playerId, $nickname);
        $player = $result[0];
        //因为后端是模糊搜索，所以需要比较下具体的id是否与用户输入相等
        if (empty($result) || $player['id'] != $playerId) {
            throw new CustomException('玩家不存在');
        }

        //判断玩家是否在社区里面
        $player['in_community'] = false;
        $community = CommunityList::query()->where('status',1)->findOrFail($request->get('community_id'))->append('member_ids');
        //判断玩家是否在社区里面
        if (in_array($playerId,$community['member_ids'])){
            $player['in_community'] = true;
        }
        return $player;
    }
}
