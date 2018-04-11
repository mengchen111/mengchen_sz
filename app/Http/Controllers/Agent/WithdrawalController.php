<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Exceptions\WithdrawalException;
use App\Http\Requests\AgentRequest;
use App\Http\Controllers\Controller;
use App\Services\WithdrawalStatisticsService;

class WithdrawalController extends Controller
{
    public $amountLimit = [
        '500', '1000', '5000', '10000', '50000'
    ];
    public $contactType = ['wechat', 'phone'];

    public function index(AgentRequest $request)
    {
        $withdrawals = auth()->user()->withdrawals();
        if ($request->has('date')) {
            $withdrawals = $withdrawals->whereDate('created_at', $request->get('date'));
        }
        $withdrawals = $withdrawals->paginate($this->per_page);
        return $withdrawals;
    }

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
        if ($rebate_balance < $amount) {
            throw new CustomException('你的余额不足,剩余提现余额为：' . $rebate_balance);
        }
        if (!in_array($amount, $this->amountLimit)) {
            throw new CustomException('提现金额不在规定范围');
        }
        $contact_type = $request->get('contact_type', 0);
        $data = $request->all();
        $data[$this->contactType[$contact_type]] = $data['contact'];

        $result = auth()->user()->withdrawals()->create($data);

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
