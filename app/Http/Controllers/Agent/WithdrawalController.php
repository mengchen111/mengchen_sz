<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Exceptions\WithdrawalException;
use App\Http\Requests\AgentRequest;
use App\Http\Controllers\Controller;
use App\Services\WithdrawalStatisticsService;
use Carbon\Carbon;

class WithdrawalController extends Controller
{
    public $amountLimit = [
        '500', '1000', '5000', '10000', '50000'
    ];
    public $contactType = ['wechat', 'phone'];

    /**
     * 获取提现申请列表(带分页)
     *
     * @SWG\Get(
     *     path="/agent/api/withdrawals",
     *     description="获取提现申请列表(带分页)",
     *     operationId="agent.withdrawals.get",
     *     tags={"rebate"},
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
     *         name="date",
     *         description="日期",
     *         in="query",
     *         required=false,
     *         type="string",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回提现申请列表",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Withdrawal"),
     *             },
     *         ),
     *     ),
     * )
     */
    public function index(AgentRequest $request)
    {
        $withdrawals = auth()->user()->withdrawals();
        if ($request->has('date')) {
            $date = Carbon::parse($request->get('date'))->format('Y-m');
            list($year, $month) = explode('-', $date);
            $withdrawals = $withdrawals->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }
        $withdrawals = $withdrawals->paginate($this->per_page);
        return $withdrawals;
    }

    /**
     * 申请提现
     *
     * @SWG\Post(
     *     path="/agent/api/withdrawals",
     *     description="代理商申请提现",
     *     operationId="agent.withdrawals.post",
     *     tags={"rebate"},
     *     consumes={"application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="amount",
     *         description="提现金额",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="contact_type",
     *         description="联系方式类型(微信-0, 电话-1)",
     *         in="formData",
     *         required=true,
     *         type="integer",
     *         default="0",
     *         enum={0, 1},
     *     ),
     *     @SWG\Parameter(
     *         name="contact",
     *         description="联系方式",
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
     *         description="提交申请成功",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Success"),
     *             },
     *         ),
     *     ),
     * )
     */
    public function store(AgentRequest $request, WithdrawalStatisticsService $statisticsService)
    {
        $this->validator($request);
        $user = auth()->user();
        $rebate_count = $statisticsService->rebateCount($user);
        $has_withdrawal = $statisticsService->hasWithdrawalCount($user);
        $wait_withdrawal = $statisticsService->waitWithdrawalCount($user);
        //提现余额
        $rebate_balance = $rebate_count - $has_withdrawal - $wait_withdrawal;
        $amount = $request->get('amount');
        //申请提现: 总返利 – 已提现金额 - 待提现 >= 提现金额
        if ($rebate_balance < $amount) {
            throw new CustomException('你的余额不足,剩余提现余额为：' . $rebate_balance);
        }
        if (!in_array($amount, $this->amountLimit)) {
            throw new CustomException('提现金额不在规定范围');
        }
        //0:wechat 1:phone
        $contact_type = $request->get('contact_type', 0);
        $data = $request->all();
        $data[$this->contactType[$contact_type]] = $data['contact'];

        $result = $user->withdrawals()->create($data);

        return $this->res('提交申请' . ($result ? '成功' : '失败'));
    }

    public function amountLimit(AgentRequest $request)
    {
        return $this->amountLimit;
    }

    protected function validator($request)
    {
        $this->validate($request, [
            'amount' => 'required',
            'contact_type' => 'required|in:0,1',
            'contact' => 'required'
        ]);
    }
}
