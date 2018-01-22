<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Http\Requests\AgentRequest;
use App\Models\CommunityList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;

class CommunityController extends Controller
{
    public function showCommunityList(AgentRequest $request)
    {
        $agentId = $request->user()->id;

        OperationLogs::add($agentId, $request->path(), $request->method(),
            '查看社区列表', $request->header('User-Agent'));

        return CommunityList::where('owner_agent_id', $agentId)->paginate($this->per_page);
    }

    public function createCommunity(AgentRequest $request)
    {
        $this->validate($request, [
            'owner_player_id' => 'required|integer',
            'name' => 'required|string|max:12',
            //'info' => 'string',
        ]);
        $agentId = $request->user()->id;
        $formData = $request->intersect(['owner_player_id', 'name', 'info']);
        $formData['owner_agent_id'] = $agentId;
        $community = CommunityList::create($formData);

        OperationLogs::add($agentId, $request->path(), $request->method(),
            '创建社区', $request->header('User-Agent'));

        return [
            'message' => '创建牌艺馆' . $community->id . '成功',
        ];
    }

    public function deleteCommunity(AgentRequest $request, $communityId)
    {
        $communityId = CommunityList::findOrFail($communityId);
        if (!empty($communityId->members)) {
            throw new CustomException('成员不为空，禁止删除');
        }
        $communityId->delete();
        return [
            'message' => '删除成功',
        ];
    }
}
