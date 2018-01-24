<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Http\Requests\AgentRequest;
use App\Models\CommunityConf;
use App\Models\CommunityList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;

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
            ->orderBy('id', 'desc')
            ->paginate($this->per_page);
    }

    public function createCommunity(AgentRequest $request)
    {
        $this->validate($request, [
            'owner_player_id' => 'required|integer',
            'name' => 'required|string|max:12|unique:community_list,name',
            //'info' => 'string',
        ]);

        $agent = $request->user();

        //检查社区数量是否达到上限
        $communityConf = $this->getCommunityConf($request->input('community_id'));
        $this->checkCommunityCreationLimit($agent, $communityConf);

        $formData = $request->intersect(['owner_player_id', 'name', 'info']);
        $formData['owner_agent_id'] = $agent->id;
        $community = CommunityList::create($formData);

        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '创建牌艺馆', $request->header('User-Agent'));

        return [
            'message' => '创建牌艺馆' . $community->id . '成功',
        ];
    }

    protected function checkCommunityCreationLimit($agent, CommunityConf $communityConf)
    {
        $existCommunityCount = CommunityList::where('owner_agent_id', $agent->id)
            ->where('status', '!=', 2)  //审批不通过的不算
            ->get()
            ->count();
        $communityCountLimit = $communityConf->max_community_count;
        if ($existCommunityCount >= $communityCountLimit) {
            throw new CustomException('最多只允许创建(加入)' . $communityCountLimit . '个牌艺馆');
        }
    }

    protected function getCommunityConf($communityId)
    {
        $communityConf = CommunityConf::where('community_id', $communityId)->first();
        if (empty($communityConf)) {
            $communityConf = CommunityConf::where('community_id', 0)->firstOrFail();
        }
        return $communityConf;
    }

    public function deleteCommunity(AgentRequest $request, $communityId)
    {
        $communityId = CommunityList::findOrFail($communityId);

        if (!empty($communityId->members)) {
            throw new CustomException('成员不为空，禁止删除');
        }
        $communityId->delete();

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除牌艺馆', $request->header('User-Agent'));

        return [
            'message' => '删除成功',
        ];
    }

    public function getCommunityDetail(AgentRequest $request, $communityId)
    {
        $community = CommunityList::with(['ownerAgent'])
            ->where('status', 1)    //只获取已审核通过的
            ->findOrFail($communityId)
            ->append('members_info');

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取牌艺馆详情', $request->header('User-Agent'));

        //获取成员信息
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
}
