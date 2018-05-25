<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\CommunityList;
use App\Services\Game\PlayerService;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     *
     * @SWG\Get(
     *     path="/api/game/player",
     *     description="根据玩家id查找玩家",
     *     operationId="game.player.get",
     *     tags={"player"},
     *
     *     @SWG\Parameter(
     *         name="player_id",
     *         description="玩家id",
     *         in="query",
     *         required=true,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="community_id",
     *         description="牌艺馆id",
     *         in="query",
     *         required=false,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="nickname",
     *         description="玩家昵称",
     *         in="query",
     *         required=false,
     *         type="string",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回玩家模型",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/GamePlayer"),
     *             },
     *             @SWG\Property(
     *                 property="in_community",
     *                 description="此玩家是否存在于查询时输入的牌艺馆(community_id)中",
     *                 type="boolean",
     *                 example=false,
     *             )
     *         ),
     *     ),
     * )
     */
    public function searchPlayer(Request $request)
    {
        $this->validate($request, [
            'player_id' => 'required|integer',
            'community_id' => 'filled|integer',
            'nickname' => 'filled|string',
        ]);

        $playerId = $request->has('player_id') ? $request->input('player_id') : '';
        $nickname = $request->has('nickname') ? $request->input('nickname') : '';

        $result = PlayerService::searchPlayers($playerId, $nickname);
        $player = $result[0];
        //因为后端是模糊搜索，所以需要比较下具体的id是否与用户输入相等
        if (empty($result) || $player['id'] != $playerId) {
            throw new CustomException('玩家不存在');
        }

        if ($request->has('community_id')) {
            //判断玩家是否在社区里面
            $player['in_community'] = false;
            $community = CommunityList::query()->where('status',1)->findOrFail($request->get('community_id'))->append('member_ids');
            //判断玩家是否在社区里面
            if (in_array($playerId,$community['member_ids'])){
                $player['in_community'] = true;
            }
        }

        return $player;
    }
}
