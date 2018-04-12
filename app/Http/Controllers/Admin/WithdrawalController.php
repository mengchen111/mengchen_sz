<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use App\Models\Withdrawal;
use App\Services\WithdrawalStatisticsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WithdrawalController extends Controller
{
    public function index(AdminRequest $request)
    {
        $this->addLog('查看提现管理列表');
        return Withdrawal::with(['user'])
            ->when($request->has('status'), function ($query) use ($request) {
                if ($request->get('status') != 4) { //全部
                    return $query->where('status', $request->get('status'));
                }
            })->when($request->has('user_id'), function ($query) use ($request) {
                return $query->where('user_id', $request->get('user_id'));
            })->latest()->paginate($this->per_page);
    }

    public function audit(AdminRequest $request, Withdrawal $withdrawal, WithdrawalStatisticsService $statisticsService)
    {
        $this->validate($request, [
            'status' => 'required|integer|in:0,1,2,3',
            'remark' => 'max:100'
        ]);
        //审核拒绝 直接改数据库
        if ($request->get('status') == 3) {
            $result = $withdrawal->update($request->all());
            return $this->res('操作' . ($result ? '成功' : '失败'));
        }
        //提现用户
        $user = $withdrawal->user;
        if ($user) {
            $rebate_count = $statisticsService->rebateCount($user);
            $has_withdrawal = $statisticsService->hasWithdrawalCount($user);
            $amount = $rebate_count - $has_withdrawal;
            //管理员审核 ：总返利 – 已提现金额 >= 提现金额
            if ($amount < $withdrawal->amount) {
                throw new CustomException('该用户提现金额不足');
            }
            $result = $withdrawal->update($request->all());

            return $this->res('操作' . ($result ? '成功' : '失败'));
        }
        throw new CustomException('用户不存在');
    }
}
