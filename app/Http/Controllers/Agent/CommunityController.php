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
    /**
     *
     * @SWG\Get(
     *     path="/agent/api/community",
     *     description="获取牌艺馆列表(带分页)",
     *     operationId="agent.community.get",
     *     tags={"community"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/sort",
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/page",
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/per_page",
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         description="状态(0-待审核,1-审核通过,2-审核不通过,3-查看全部)",
     *         in="query",
     *         required=false,
     *         type="integer",
     *         enum={0, 1, 2, 3},
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回牌艺馆列表(带分页)",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Community"),
     *             },
     *             @SWG\Property(
     *                 property="owner_agent",
     *                 type="object",
     *                 allOf={@SWG\Schema(ref="#/definitions/User")},
     *             )
     *         ),
     *     ),
     * )
     */
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
            'owner_player_id' => 'required|integer|between:10000,999999',
            'name' => 'required|string|max:12|unique:community_list,name',
            'info' => 'max:12',
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
            '创建牌艺馆', $request->header('User-Agent'), json_encode($request->all()));

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

    /**
     *
     * @SWG\Get(
     *     path="/agent/api/community/info/{community_id}",
     *     description="查看某个牌艺馆的信息(只能查看自己拥有的)",
     *     operationId="community.info.get",
     *     tags={"community"},
     *
     *     @SWG\Parameter(
     *         name="community_id",
     *         description="牌艺馆id",
     *         in="path",
     *         required=true,
     *         type="integer",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回此牌艺馆信息",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Community"),
     *             },
     *         ),
     *     ),
     *
     *     @SWG\Response(
     *         response=404,
     *         description="未找到此牌艺馆",
     *     ),
     * )
     */
    public function getCommunityInfo(AgentRequest $request, $communityId)
    {
        $agent = $request->user();

        $community = CommunityList::where('owner_agent_id', $agent->id) //只能获取他拥有的牌艺馆信息
            ->findOrFail($communityId);

        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '查看单个牌艺馆基本信息', $request->header('User-Agent'));

        return $community;
    }

    /**
     *
     * @SWG\Get(
     *     path="/agent/api/community/detail/{community_id}",
     *     description="查看某个牌艺馆的详细信息",
     *     operationId="agent.community.detail.get",
     *     tags={"community"},
     *
     *     @SWG\Parameter(
     *         name="community_id",
     *         description="牌艺馆id",
     *         in="path",
     *         required=true,
     *         type="integer",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回此牌艺馆详细信息",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Community"),
     *             },
     *             @SWG\Property(
     *                 property="owner_agent",
     *                 description="牌艺馆主信息",
     *                 type="object",
     *                 allOf={
     *                     @SWG\Schema(ref="#/definitions/User"),
     *                 },
     *             ),
     *             @SWG\Property(
     *                 property="application_data",
     *                 description="成员申请信息",
     *                 type="object",
     *                 @SWG\Property(
     *                     property="application_count",
     *                     description="申请数量",
     *                     type="integer",
     *                     example=1,
     *                 ),
     *                 @SWG\Property(
     *                     property="applications",
     *                     description="申请记录",
     *                     type="array",
     *                     @SWG\Items(
     *                         type="object",
     *                         allOf={@SWG\Schema(ref="#/definitions/CommunityInvitationApplication"),},
     *                         @SWG\Property(
     *                             property="player",
     *                             type="object",
     *                             allOf={
     *                                 @SWG\Schema(ref="#/definitions/GamePlayerSimplified"),
     *                             },
     *                         ),
     *                     ),
     *                 ),
     *             ),
     *             @SWG\Property(
     *                 property="members_info",
     *                 description="成员信息",
     *                 type="array",
     *                 @SWG\Items(
     *                     type="object",
     *                     allOf={@SWG\Schema(ref="#/definitions/GamePlayerSimplified"),},
     *                 ),
     *             ),
     *             @SWG\Property(
     *                 property="member_log",
     *                 description="牌艺馆成员动态信息",
     *                 type="object",
     *                 @SWG\Property(
     *                     property="has_read",
     *                     description="牌艺馆动态是否已读标识(0-未读,1-已读)",
     *                     type="integer",
     *                     example=1,
     *                 ),
     *                 @SWG\Property(
     *                     property="member_logs",
     *                     description="牌艺馆动态记录(只显示最新的30条)",
     *                     type="array",
     *                     @SWG\Items(
     *                         type="object",
     *                         allOf={@SWG\Schema(ref="#/definitions/CommunityMemberLog"),},
     *                         @SWG\Property(
     *                             property="player",
     *                             type="object",
     *                             allOf={
     *                                 @SWG\Schema(ref="#/definitions/GamePlayerSimplified"),
     *                             },
     *                         ),
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     *
     *     @SWG\Response(
     *         response=404,
     *         description="未找到此牌艺馆",
     *     ),
     * )
     */
    public function getCommunityDetail(AgentRequest $request, $communityId)
    {
        $agent = $request->user();

        $community = CommunityList::with(['ownerAgent'])
            ->where('owner_agent_id', $agent->id)   //只能获取他拥有的牌艺馆信息
            ->where('status', 1)    //只能获取已审核通过的
            ->findOrFail($communityId)
            ->append('members_info')        //成员信息
            ->append('application_data')    //此社区的申请列表
            ->append('member_log');         //社区动态

        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '获取牌艺馆详情', $request->header('User-Agent'));

        return $community;
    }

    /**
     *
     * @SWG\Put(
     *     path="/agent/api/community/info/{community_id}",
     *     description="更新牌艺馆信息",
     *     operationId="community.info.put",
     *     tags={"community"},
     *
     *     @SWG\Parameter(
     *         name="community_id",
     *         description="牌艺馆id",
     *         in="path",
     *         required=true,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="info",
     *         description="牌艺馆简介",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         description="牌艺馆名字",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *
     *     @SWG\Response(
     *         response=422,
     *         description="参数验证错误",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/ValidationError"),
     *             },
     *         ),
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="更新成功",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Success"),
     *             },
     *         ),
     *     ),
     * )
     */
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
            '更新牌艺馆信息', $request->header('User-Agent'), json_encode($request->all()));

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

    /**
     *
     * @SWG\Get(
     *     path="/agent/api/communities",
     *     description="获取此代理商已审核通过的牌艺馆信息",
     *     operationId="communities.get",
     *     tags={"community"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回牌艺馆信息和牌艺馆id",
     *         @SWG\Property(
     *             type="object",
     *             @SWG\Property(
     *                 property="communities",
     *                 description="牌艺馆信息集合",
     *                 type="array",
     *                 @SWG\Items(
     *                     type="object",
     *                     allOf={
     *                         @SWG\Schema(ref="#/definitions/Community"),
     *                     },
     *                 ),
     *             ),
     *             @SWG\Property(
     *                 property="community_ids",
     *                 description="牌艺馆id数组",
     *                 type="array",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="10000",
     *                 ),
     *                 example={"10000","10001"},
     *             ),
     *         ),
     *     ),
     * )
     */
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
