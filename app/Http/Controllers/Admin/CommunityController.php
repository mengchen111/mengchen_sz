<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use App\Services\Game\GameApiService;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use App\Models\CommunityList;
use App\Exceptions\CustomException;
use App\Services\CommunityService;

class CommunityController extends Controller
{
    public function showCommunityList(AdminRequest $request)
    {
        $admin = $request->user();
        OperationLogs::add($admin->id, $request->path(), $request->method(),
            '查看牌艺馆列表', $request->header('User-Agent'));

        return CommunityList::with(['ownerAgent'])
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

    public function createCommunity(AdminRequest $request)
    {
        $this->validate($request, [
            'owner_player_id' => 'required|integer',
            'owner_agent_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:12|unique:community_list,name',
            'info' => 'required|string|max:12',
        ]);

        //检查社区数量是否达到上限(根据代理商来查找配置)
        $communityConf = CommunityService::getCommunityConf();
        $this->checkCommunityCreationLimit($request->input('owner_agent_id'), $request->input('owner_player_id'), $communityConf);

        $formData = $request->intersect(['owner_player_id', 'owner_agent_id', 'name', 'info']);
        $formData['id'] = CommunityService::getRandomId();  ////获取随机社团id
        $community = CommunityList::create($formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '创建牌艺馆', $request->header('User-Agent'));

        return [
            'message' => '创建牌艺馆' . $community->id . '成功, 等待管理员审核',
        ];
    }

    public function deleteCommunity(AdminRequest $request, $communityId)
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

    public function auditCommunityApplication(AdminRequest $request, CommunityList $community)
    {
        $this->validate($request, [
            'status' => 'required|integer|in:1,2',
        ]);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '审批牌艺馆', $request->header('User-Agent'));

        $community->status = $request->input('status');
        $community->save();

        return [
            'message' => '操作成功'
        ];
    }

    public function changeCommunityOwnership(AdminRequest $request)
    {
        //todo 更改牌艺馆馆长，查询members里面是否有馆长和新馆长，删除之
    }

    public function getCommunityValidCardConsumedLog(AdminRequest $request)
    {
        $this->validate($request, [
            'community_id' => 'nullable|integer',
            'start_time' => 'nullable|required_with_all:end_time|date_format:"Y-m-d H:i:s"',
            'end_time' => 'nullable|required_with_all:start_time|date_format:"Y-m-d H:i:s"',
            'data_type' => 'required|string|in:detail,summary',
        ]);
        $params = $request->intersect(['community_id', 'start_time', 'end_time']);

        //当搜索条件为空时，不查询
        if (empty($params)) {
            $data['currency_log'] = [];
            $data['summary'] = [
                'total_consumed' => 0,
            ];
        } else {
            $cardConsumedLogApi = config('custom.game_api_currency_log');
            $currencyLog = GameApiService::request('GET', $cardConsumedLogApi, $params);
            $data['currency_log'] = collect($currencyLog)->filter(function ($item) {
                return $item['community_id'] != 0;
            })->toArray();
            $data['summary'] = [
                'total_consumed' => collect($data['currency_log'])->sum('val'),
            ];
        }

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看牌艺馆有效耗卡', $request->header('User-Agent'), json_encode($request->all()));

        return $request->input('data_type') === 'detail'
            ? Paginator::paginate($data['currency_log'], $this->per_page, $this->page)
            : $data['summary'];
    }
}