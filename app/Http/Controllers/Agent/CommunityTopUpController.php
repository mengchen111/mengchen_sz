<?php

namespace App\Http\Controllers\Agent;

use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRequest;
use App\Models\CommunityList;
use App\Models\User;
use App\Models\OperationLogs;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use App\Models\CommunityCardTopupLog;

class CommunityTopUpController extends Controller
{
    public function topUpCommunity(AgentRequest $request)
    {
        $this->validate($request, [
            'community_id' => 'required|integer',
            'item_type_id' => 'required|integer|exists:item_type,id',
            'item_amount' => 'required|integer',
            'remark' => 'string|max:255',
        ]);
        $topUpForm = $request->intersect(['community_id', 'item_type_id', 'item_amount', 'remark']);
        $agent = User::with(['inventory' => function ($query) use ($topUpForm) {
            $query->where('item_id', $topUpForm['item_type_id']);
        }])->find($request->user()->id);

        //检查库存是否足够
        if (empty($agent->inventory) or $agent->inventory->stock < $topUpForm['item_amount']) {
            throw new CustomException('库存不足无法充值');
        }

        //检查代理商是否拥有此牌艺馆
        $community = CommunityList::find($topUpForm['community_id']);
        if (empty($community)) {
            throw new CustomException('此牌艺馆不存在');
        }
        if ($community->owner_agent_id != $agent->id) {
            throw new CustomException('您不是此牌艺馆的馆主，无法为其充值');
        }

        $this->topUp4Community($agent, $community, $topUpForm);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '充值牌艺馆道具', $request->header('User-Agent'));

        return [
            'message' => '充值成功',
        ];
    }

    protected function topUp4Community($agent, $community, $topUpForm)
    {
        DB::transaction(function () use ($agent, $community, $topUpForm) {
            //减代理商的库存
            $agent->inventory->stock -= $topUpForm['item_amount'];
            $agent->inventory->save();

            //加社区的库存
            $community->card_stock += $topUpForm['item_amount'];
            $community->save();

            //记录充值流水
            CommunityCardTopupLog::create([
                'community_id' => $community->id,
                'agent_id' => $agent->id,
                'item_type_id' => $topUpForm['item_type_id'],
                'item_amount' => $topUpForm['item_amount'],
                'remark' => $topUpForm['remark'],
            ]);
        });
    }

    protected function getTopUpHistory(AgentRequest $request)
    {
        $this->validate($request, [
            'item_type_id' => 'required|integer|in:1,2',
            //'community_id' => 'integer',
        ]);
        $agent = $request->user();

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看牌艺馆', $request->header('User-Agent'));

         return CommunityCardTopupLog::where('agent_id', $agent->id)
            ->where('item_type_id', $request->input('item_type_id'))
            ->when($request->has('filter'), function ($query) use ($request) {
                return $query->where('community_id', 'like', "%{$request->input('filter')}%");
            })
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }
}
