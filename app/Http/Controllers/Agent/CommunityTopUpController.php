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
    /**
     *
     * @SWG\Post(
     *     path="/agent/api/community/card/top-up",
     *     description="代理商给牌艺馆充值",
     *     operationId="agent.community.card.top-up",
     *     tags={"community-top-up"},
     *     consumes={"application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="community_id",
     *         description="牌艺馆id",
     *         in="formData",
     *         required=true,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="item_amount",
     *         description="充值数量",
     *         in="formData",
     *         required=true,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="item_type_id",
     *         description="充值道具类型id(目前只有房卡-1)",
     *         in="formData",
     *         required=true,
     *         type="integer",
     *         default=1,
     *         enum={1},
     *     ),
     *     @SWG\Parameter(
     *         name="remark",
     *         description="备注",
     *         in="formData",
     *         required=false,
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
     *         description="充值成功",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Success"),
     *             },
     *         ),
     *     ),
     * )
     */
    public function topUpCommunity(AgentRequest $request)
    {
        $this->validate($request, [
            'community_id' => 'required|integer',
            'item_type_id' => 'required|integer|exists:item_type,id',
            'item_amount' => 'required|integer|min:1',
            'remark' => 'max:255',
        ]);
        $topUpForm = $request->only(['community_id', 'item_type_id', 'item_amount', 'remark']);
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
            '充值牌艺馆道具', $request->header('User-Agent'), json_encode($request->all()));

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

    /**
     *
     * @SWG\Get(
     *     path="/agent/api/community/card/top-up-history",
     *     description="获取牌艺馆充值记录(带分页)",
     *     operationId="community.top-up-history.get",
     *     tags={"community-top-up"},
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
     *         name="item_type_id",
     *         description="道具id(目前只有一种，传1)",
     *         in="query",
     *         required=true,
     *         type="integer",
     *         default=1,
     *     ),
     *     @SWG\Parameter(
     *         name="filter",
     *         description="搜索牌艺馆id",
     *         in="query",
     *         required=false,
     *         type="integer",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回充值记录信息",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/CommunityCardTopUpLog"),
     *             },
     *             @SWG\Property(
     *                 property="item",
     *                 description="充值道具信息",
     *                 type="object",
     *                 allOf={
     *                     @SWG\Schema(ref="#/definitions/ItemType"),
     *                 },
     *             ),
     *         ),
     *     ),
     * )
     */
    protected function getTopUpHistory(AgentRequest $request)
    {
        $this->validate($request, [
            'item_type_id' => 'required|integer|in:1,2',
            //'community_id' => 'integer',
        ]);
        $agent = $request->user();

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看牌艺馆充值历史', $request->header('User-Agent'), json_encode($request->all()));

         return CommunityCardTopupLog::with(['item'])
            ->where('agent_id', $agent->id)
            ->where('item_type_id', $request->input('item_type_id'))
            ->when($request->has('filter'), function ($query) use ($request) {
                return $query->where('community_id', 'like', "%{$request->input('filter')}%");
            })
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }
}
