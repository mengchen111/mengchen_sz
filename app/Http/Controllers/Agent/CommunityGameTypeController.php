<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\AgentRequest;
use App\Models\CommunityGameRules;
use App\Models\CommunityList;
use App\Traits\GameTypeMap;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Game\GameOptionsService;

class CommunityGameTypeController extends Controller
{
    use GameTypeMap;

    /**
     * 获取牌艺馆所关联的游戏包的所有游戏的游戏规则(可选的游戏规则)
     *
     * @param AdminRequest $request
     * @param CommunityList $community
     * @throws CustomException
     */
    public function getGameRules(AgentRequest $request, CommunityList $community, GameOptionsService $gameOptionsService)
    {
        $gameRules = [];
        foreach ($community->game_group_game_types as $gameTypeId => $gameTypeName) {
            $gameRules[$gameTypeId] = $gameOptionsService->getCategoricalOption($gameTypeId);
        }

        $this->addLog('获取牌艺馆所关联的游戏包的所有游戏的游戏规则(可选的游戏规则)');
        return $gameRules;
    }

    /**
     * 创建/编辑游戏默认规则
     * @param AgentRequest $request
     * @param CommunityList $community
     * @param GameOptionsService $gameOptionsService
     * @return array
     */
    public function modifyGameRuleTemplate(AgentRequest $request, CommunityList $community, GameOptionsService $gameOptionsService)
    {
        $communityGameTypeIds = array_keys($community->game_group_game_types);  //此牌艺馆可用的游戏类型
        $this->validate($request, [
            'game_type' => 'required|in:' . implode(',', $communityGameTypeIds),
            'rounds' => 'filled|integer',
            'players' => 'filled|integer',
            'wanfa' => 'filled',
            'hua_pai' => 'filled|integer',
            'gui_pai' => 'filled|integer',
            'ma_pai' => 'filled|integer',
            'di_fen' => 'filled|integer',
            'qing_hun' => 'filled|integer',
            'score_limit' => 'filled|integer',
        ]);

        $options = array_filter($request->only([
            'rounds', 'players', 'wanfa', 'hua_pai', 'gui_pai', 'ma_pai', 'di_fen', 'qing_hun', 'score_limit',
        ]), function ($val) {
            return $val !== null;
        });

        CommunityGameRules::updateOrCreate([
            'community_id' => $community->id,
            'game_type_id' => $request->game_type,
        ], [
            'rule' => $options,
        ]);

        $this->addLog('编辑/创建游戏默认规则模版');

        return $this->res('操作成功');
    }

    /**
     * 获取某个牌艺馆的某个游戏类型的游戏规则模版
     * @param AgentRequest $request
     * @param GameOptionsService $gameOptionsService
     */
    public function getGameRule(AgentRequest $request, GameOptionsService $gameOptionsService)
    {
        $this->validate($request, [
            'game_type' => 'required|in:' . implode(',', $this->getGameTypeIds()),
            'community_id' => 'required|exists:community_list,id',
        ]);

        $this->addLog('查看牌艺馆游戏规则模版');

        return CommunityGameRules::where('community_id', $request->community_id)
            ->where('game_type_id', $request->game_type)
            ->first();
    }
}
