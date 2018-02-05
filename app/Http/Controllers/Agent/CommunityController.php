<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Http\Requests\AgentRequest;
use App\Models\CommunityCardTopupLog;
use App\Models\CommunityConf;
use App\Models\CommunityList;
use App\Services\CommunityService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CommunityController extends Controller
{
    public function showCommunityList(AgentRequest $request)
    {
        $agent = $request->user();
        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '查看牌艺馆列表', $request->header('User-Agent'));

        return CommunityList::with(['ownerAgent'])
            ->where('owner_agent_id', $agent->id)
            ->when($request->has('status'), function ($query) use ($request) {
                if ((int)$request->input('status') === 3) {     //返回所有状态的社区列表
                    return $query;
                }
                return $query->where('status', $request->input('status'));
            })
            //查找指定的社区
            ->when($request->has('community_id'), function ($query) use ($request) {
                return $query->where('id', $request->input('community_id'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->per_page);
    }

    public function createCommunity(AgentRequest $request)
    {
        $this->validate($request, [
            'owner_player_id' => 'required|integer',
            'name' => 'required|string|max:12|unique:community_list,name',
            'info' => 'required|string|max:12',
        ]);

        $agent = $request->user();

        //检查社区数量是否达到上限(根据代理商来查找配置)
        $communityConf = CommunityService::getCommunityConf();
        $this->checkCommunityCreationLimit($agent->id, $request->input('owner_player_id'), $communityConf);

        $formData = $request->intersect(['owner_player_id', 'name', 'info']);
        $formData['owner_agent_id'] = $agent->id;
        $formData['id'] = CommunityService::getRandomId();  //获取随机社团id
        $community = CommunityList::create($formData);

        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '创建牌艺馆', $request->header('User-Agent'));

        return [
            'message' => '创建牌艺馆' . $community->id . '成功, 等待管理员审核',
        ];
    }

    protected function checkCommunityCreationLimit($agentId, $playerId, $communityConf)
    {
        $existPendingCommunityCount = CommunityList::where('owner_agent_id', $agentId)
            ->where('status', '=', 0)  //此代理商申请的待审批的社团数
            ->get()
            ->count();
        //可申请的最大待审核牌艺馆数量
        $communityPendingCountLimit = $communityConf->max_community_pending_count;
        if ($existPendingCommunityCount >= $communityPendingCountLimit) {
            throw new CustomException('每个代理商最多只允许创建' . $communityPendingCountLimit . '个待审核牌艺馆');
        }
        //玩家可加入的最大牌艺馆数量(包括创建和加入，审核的时候也需要执行此步骤(理论上不需要))
        $communityLimit = $communityConf->max_community_count;
        $playerInvolvedCommunitiesCount = CommunityService::playerInvolvedCommunitiesTotalCount($playerId);
        if ($playerInvolvedCommunitiesCount >= $communityLimit) {
            throw new CustomException('每个玩家最多只可以加入(包括拥有)' . $communityLimit . '个牌艺馆');
        }
    }

    public function deleteCommunity(AgentRequest $request, CommunityList $community)
    {
        if (!empty($community->members)) {
            throw new CustomException('成员不为空，禁止删除');
        }
        $community->delete();

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除牌艺馆', $request->header('User-Agent'));

        return [
            'message' => '删除成功',
        ];
    }

    public function getCommunityDetail(AgentRequest $request, $communityId)
    {
        $community = CommunityList::with(['ownerAgent'])
            ->where('status', 1)    //只能获取已审核通过的
            ->findOrFail($communityId)
            ->append('members_info')        //成员信息
            ->append('application_data')    //此社区的申请列表
            ->append('member_log');         //社区动态

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取牌艺馆详情', $request->header('User-Agent'));

        return $community;
    }

    public function updateCommunityInfo(AgentRequest $request, CommunityList $community)
    {
        $this->validate($request, [
            'name' => 'required|max:12',
            'info' => 'required|string|max:12',
        ]);
        $formData = $request->intersect(['name', 'info']);

        $newCommunityName = $formData['name'];
        if ($community->name !== $newCommunityName) {
            $this->checkDuplicateName($newCommunityName);
        }

        $community->update($formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '更新牌艺馆信息', $request->header('User-Agent'));

        return [
            'message' => '更新牌艺馆信息成功',
        ];
    }

    protected function checkDuplicateName($newName)
    {
        $sameNameCommunities = CommunityList::where('name', $newName)->get();
        if ($sameNameCommunities->count() >= 1) {
            throw new CustomException('牌艺馆名字重复，请使用其它名字');
        }
        return true;
    }

    public function getAgentOwnerCommunities(AgentRequest $request)
    {
        $agent = $request->user();
        $communities = $agent->communities();

        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '代理商已审核过的牌艺馆(无分页)', $request->header('User-Agent'));

        $result['community_ids'] = $communities
            ->pluck('id')
            ->map(function ($item) {
                return (string) $item;  //将id改为string类型，不然js的vSelect插件报错
            });
        $result['communities'] = $communities;

        return $result;
    }
}
