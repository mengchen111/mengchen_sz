<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Http\Requests\AgentRequest;
use App\Models\CommunityInvitationApplication;
use App\Models\CommunityList;
use App\Models\CommunityMemberLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\DB;

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
        $formData = $request->only(['player_id', 'community_id']);
        $formData['status'] = 0;    //状态设置为pending
        $formData['type'] = 1;      //群主邀请

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '邀请加入牌艺馆', $request->header('User-Agent'));

        $invitation = CommunityInvitationApplication::where('player_id', $formData['player_id'])
            ->where('community_id', $formData['community_id'])
            ->where('status', 0)
            ->first();

        //已经存在的邀请
        if (!empty($invitation)) {
            throw new CustomException('已经邀请过此玩家');
        }

        CommunityInvitationApplication::create($formData);

        return [
            'message' => '邀请成功',
        ];
    }

    //群主同意入群申请
    public function approveApplication(AgentRequest $request)
    {
        $request->validate($request, [
            'player_id' => 'required|integer',
            'community_id' => 'required|integer',
        ]);

        $application = CommunityInvitationApplication::where('player_id', $request->input('player_id'))
            ->where('community_id', $request->input('community_id'))
            ->where('type', 0)  //类型为用户申请的入群请求（1为群主主动邀请）
            ->where('status', 0)    //状态为pending的申请
            ->firstOrFail();
        $agent = $request->user();
        $community = CommunityList::findOrFail($request->input('community_id'));
        $this->checkoutCommunityOwnership($community, $agent);    //检查此代理商是否拥有此群

        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '同意牌艺馆入馆申请', $request->header('User-Agent'));

        $this->doApproveApplication($community, $application);

        return [
            'message' => '审批通过',
        ];
    }

    //群主拒绝入群申请
    public function declineApplication(AgentRequest $request)
    {
        $request->validate($request, [
            'player_id' => 'required|integer',
            'community_id' => 'required|integer',
        ]);

        $application = CommunityInvitationApplication::where('player_id', $request->input('player_id'))
            ->where('community_id', $request->input('community_id'))
            ->where('type', 0)  //类型为用户申请的入群请求（1为群主主动邀请）
            ->where('status', 0)    //状态为pending的申请
            ->firstOrFail();
        $agent = $request->user();
        $community = CommunityList::findOrFail($request->input('community_id'));
        $this->checkoutCommunityOwnership($community, $agent);    //检查此代理商是否拥有此群

        OperationLogs::add($agent->id, $request->path(), $request->method(),
            '拒绝牌艺馆入馆申请', $request->header('User-Agent'));

        $this->doDeclineApplication($application);

        return [
            'message' => '拒绝审批完成',
        ];
    }

    protected function checkoutCommunityOwnership($community, $agent)
    {
        if ($community->owner_agent_id !== $agent->id) {
            throw new CustomException('您不是此牌艺馆的馆主，无法审批入馆请求');
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

        $community = CommunityList::findOrFail($request->input('community_id'));
        $playerId = $request->input('player_id');
        $agent = $request->user();
        $this->checkoutCommunityOwnership($community, $agent);

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
        });
    }
}
