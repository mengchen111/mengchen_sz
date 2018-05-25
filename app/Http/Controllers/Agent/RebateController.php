<?php

namespace App\Http\Controllers\Agent;

use App\Http\Requests\AgentRequest;
use App\Services\WithdrawalStatisticsService;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class RebateController extends Controller
{
    /**
     *
     * @SWG\Get(
     *     path="/agent/api/rebates",
     *     description="获取此代理商的佣金记录详情（带分页）",
     *     operationId="agent.rebates.get",
     *     tags={"rebate"},
     *
     *     @SWG\Parameter(
     *         name="sort",
     *         description="排序(id|desc)",
     *         in="query",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="page",
     *         description="第几页",
     *         in="query",
     *         required=false,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="per_page",
     *         description="每页多少条数据",
     *         in="query",
     *         required=false,
     *         type="integer",
     *         default="15",
     *     ),
     *     @SWG\Parameter(
     *         name="date",
     *         description="搜索日期",
     *         in="query",
     *         required=false,
     *         type="integer",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回佣金记录（分页）",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Rebate"),
     *             },
     *             @SWG\Property(
     *                 property="rule",
     *                 description="返利规则",
     *                 type="object",
     *                     allOf={
     *                         @SWG\Schema(ref="#/definitions/RebateRule"),
     *                     },
     *             ),
     *             @SWG\Property(
     *                 property="children",
     *                 description="下级代理商信息",
     *                 type="object",
     *                     @SWG\Property(
     *                         property="id",
     *                         description="下级代理商id",
     *                         type="integer",
     *                         example=10,
     *                     ),
     *                     @SWG\Property(
     *                         property="name",
     *                         description="下级代理商昵称",
     *                         type="string",
     *                         example="aaa",
     *                     ),
     *                     @SWG\Property(
     *                         property="group_id",
     *                         description="下级代理商组id",
     *                         type="integer",
     *                         example=3,
     *                     ),
     *                     @SWG\Property(
     *                         property="group",
     *                         description="下级代理商组信息",
     *                         type="object",
     *                             allOf={
     *                                 @SWG\Schema(ref="#/definitions/Group"),
     *                             },
     *                     ),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function index(AgentRequest $request)
    {
        $rebates = auth()->user()->rebates()->with(['children' => function ($query) {
            $query->select('id', 'name', 'group_id')->with(['group']);
        }, 'rule']);
        if ($request->has('date')) {
            $date = Carbon::parse($request->get('date'))->format('Y-m');
            list($year, $month) = explode('-', $date);
            $rebates = $rebates->whereYear('rebate_at', $year)->whereMonth('rebate_at', $month);
        }
        $rebates = $rebates->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
        return $rebates;
    }

    /**
     *
     * @SWG\Get(
     *     path="/agent/api/rebates/statistics",
     *     description="获取此代理商的佣金状态",
     *     operationId="agent.rebates.statistics.get",
     *     tags={"rebate"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回佣金信息",
     *         @SWG\Property(
     *             type="object",
     *             @SWG\Property(
     *                 property="has_withdrawal",
     *                 description="已提现总额",
     *                 type="integer",
     *                 example=0,
     *             ),
     *             @SWG\Property(
     *                 property="rebate_balance",
     *                 description="可提现余额",
     *                 type="integer",
     *                 example=0,
     *             ),
     *             @SWG\Property(
     *                 property="rebate_count",
     *                 description="累计返利总额",
     *                 type="integer",
     *                 example=0,
     *             ),
     *             @SWG\Property(
     *                 property="wait_withdrawal",
     *                 description="等待提现总额",
     *                 type="integer",
     *                 example=0,
     *             ),
     *         ),
     *     ),
     * )
     */
    public function statistics(AgentRequest $request,WithdrawalStatisticsService $statisticsService)
    {
        $user = auth()->user();
        $data['rebate_count'] = $statisticsService->rebateCount($user);
        $data['has_withdrawal'] = $statisticsService->hasWithdrawalCount($user);
        $data['wait_withdrawal'] = $statisticsService->waitWithdrawalCount($user);
        //可提现余额
        $data['rebate_balance'] = $data['rebate_count'] - $data['has_withdrawal'] - $data['wait_withdrawal'];
        return $data;
    }


}
