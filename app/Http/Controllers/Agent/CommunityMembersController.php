<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Http\Requests\AgentRequest;
use App\Models\CommunityInvitationApplication;
use App\Models\CommunityList;
use App\Models\CommunityMemberLog;
use App\Services\CommunityService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\Game\GameApiService;

class CommunityMembersController extends Controller
{
    //邀请入群
    public function inviteMember(AgentRequest $request)
    {
        $this->validate($request, [
            'player_id' => 'required|integer',
            //'type' => 'required|integer|in:0,1',
            'community_id' => 'required|integer',
        ]);
        $playerId = $request->input('player_id');
        $communityId = $request->input('community_id');

        //检查玩家是否已经在群中
        $this->checkIfInTheCommunity($playerId, $communityId);
        //检查是否已经存在的邀请
        $this->checkIfDuplicatedInvitation($playerId, $communityId);

        $formData = $request->only(['player_id', 'community_id']);
        $formData['status'] = 0;    //状态设置为pending
        $formData['type'] = 1;      //群主邀请

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '邀请加入牌艺馆', $request->header('User-Agent'));

        CommunityInvitationApplication::create($formData);

        return [
            'message' => '邀请成功',
        ];
    }

    protected function checkIfInTheCommunity($playerId, $communityId)
    {
        $community = CommunityList::findOrFail($communityId);
        if ($community->ifHasMember($playerId)) {
            throw new CustomException('此玩家已处于当前牌艺馆中');
        }
        if ((int)$community->owner_player_id === $playerId) {
            throw new CustomException('您已经是此牌艺馆的馆主，不能邀请自己');
        }
        return true;
    }

    protected function checkIfDuplicatedInvitation($playerId, $communityId)
    {
        $invitation = CommunityInvitationApplication::where('player_id', $playerId)
            ->where('community_id', $communityId)
            ->where('status', 0)
            ->first();

        if (!empty($invitation)) {
            throw new CustomException('已经邀请过此玩家');
        }

        return true;
    }

    //群主同意入群申请
    public function approveApplication(AgentRequest $request, CommunityInvitationApplication $application)
    {
        if ((int)$application->status !== 0) {
            throw new CustomException('此条申请已被审批');
        }
        $agent = $request->user();
        $community = CommunityList::findOrFail($application->community_id);
        $this->checkCommunityOwnership($community, $agent);    //检查此代理商是否拥有此群
        $this->checkPlayerCommunityLimit($application->player_id, $application->community_id);

        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '同意牌艺馆入馆申请', $request->header('User-Agent'));

        $this->doApproveApplication($community, $application);

        return [
            'message' => '申请通过',
        ];
    }

    //群主拒绝入群申请
    public function declineApplication(AgentRequest $request, CommunityInvitationApplication $application)
    {
        if ((int)$application->status !== 0) {
            throw new CustomException('此条申请已被审批');
        }
        $agent = $request->user();
        $community = CommunityList::findOrFail($application->community_id);
        $this->checkCommunityOwnership($community, $agent);    //检查此代理商是否拥有此群

        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '拒绝牌艺馆入馆申请', $request->header('User-Agent'));

        $this->doDeclineApplication($application);

        return [
            'message' => '拒绝申请完成',
        ];
    }

    protected function checkCommunityOwnership($community, $agent)
    {
        if ($community->owner_agent_id !== $agent->id) {
            throw new CustomException('您不是此牌艺馆的馆主，无法审批入馆请求');
        }
    }

    protected function checkPlayerCommunityLimit($playerId, $communityId)
    {
        $communityConf = CommunityService::getCommunityConf($communityId);
        $communityLimit = $communityConf->max_community_count;
        $playerInvolvedCommunitiesCount = CommunityService::playerInvolvedCommunitiesTotalCount($playerId);
        if ($playerInvolvedCommunitiesCount >= $communityLimit) {
            throw new CustomException('每个玩家最多只可以加入(包括拥有)' . $communityLimit . '个牌艺馆');
        }
    }

    protected function doApproveApplication($community, $application)
    {
        DB::transaction(function () use ($community, $application) {
            $application->status = 1;   //更新申请状态为已通过
            $application->save();

            //添加成员到community_list中相应的行中
            $newMembers = [];
            array_push($newMembers, $application->player_id);
            $community->addMembers($newMembers);

            //记录成员变动日志
            CommunityMemberLog::create([
                'community_id' => $application->community_id,
                'player_id' => $application->player_id,
                'action' => '加入',
            ]);

            //todo 社区加入新的玩家需要通知游戏后端
        });
    }

    protected function doDeclineApplication($application)
    {
        DB::transaction(function () use ($application) {
            $application->status = 2;   //更新申请状态为已拒绝
            $application->save();
        });
    }

    public function kickOutMember(AgentRequest $request)
    {
        $this->validate($request, [
            'community_id' => 'required|integer|exists:community_list,id',
            'player_id' => 'required|integer',
        ]);

        //todo 踢成员之前需要先检查其是否在游戏中，要调用后端接口
        $this->checkIfPlayerInGame($request->input('player_id'));

        $community = CommunityList::findOrFail($request->input('community_id'));
        $playerId = $request->input('player_id');
        $agent = $request->user();
        $this->checkCommunityOwnership($community, $agent);

        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '从牌艺馆中踢出成员', $request->header('User-Agent'));

        if (! $community->ifHasMember($playerId)) {
            throw new CustomException('此玩家不存在与该牌艺馆，无法踢出');
        }
        $this->doKickOutMember($community, $playerId);

        return [
            'message' => '踢出成员成功',
        ];
    }

    protected function checkIfPlayerInGame($playerId)
    {
        //获取正在玩的房间数据
        $api = config('custom.game_api_room_open');
        $openRooms = GameApiService::request('GET', $api);
        $inGameUids = [];
        foreach ($openRooms as $openRoom) {
            $uids = collect($openRoom)
                ->only(['creator_uid', 'uid_1', 'uid_2', 'uid_3', 'uid_4'])
                ->flatten()
                ->toArray();
            $inGameUids = array_merge($inGameUids, $uids);
        }
        if (in_array($playerId, $inGameUids)) {
            throw new CustomException('此玩家正在游戏中，禁止踢出操作');
        }
        return true;
    }

    protected function doKickOutMember($community, $playerId)
    {
        DB::transaction(function () use ($community, $playerId) {
            //踢出成员
            $abandonedMembers = [];
            array_push($abandonedMembers, $playerId);
            $community->deleteMembers($abandonedMembers);

            //记录成员变动日志
            CommunityMemberLog::create([
                'community_id' => $community->id,
                'player_id' => $playerId,
                'action' => '踢出',
            ]);

            //todo 社区踢出玩家需要通知游戏后端
        });
    }

    public function readCommunityLog(AgentRequest $request, CommunityList $community)
    {
        $communityId = $community->id;
        $cacheKey = config('custom.cache_community_log') . $communityId;
        $cacheData = Cache::get($cacheKey);
        $cacheData['has_read'] = 1;
        Cache::forever($cacheKey, $cacheData);
    }
}
