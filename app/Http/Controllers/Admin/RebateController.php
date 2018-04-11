<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use App\Models\User;
use App\Services\WithdrawalStatisticsService;
use App\Http\Controllers\Controller;

class RebateController extends Controller
{
    public function showUserRebate(AdminRequest $request, User $user)
    {
        $this->addLog('查看代理商返利列表');
        return $user->rebates()->with(['children'=>function($query){
            $query->select('id', 'name', 'group_id')->with(['group']);
        }, 'user','rule'])->latest()->paginate($this->per_page);
    }

    public function statistics(AdminRequest $request, User $user,WithdrawalStatisticsService $statisticsService)
    {
        $data['rebate_count'] = $statisticsService->rebateCount($user);
        $data['has_withdrawal'] = $statisticsService->hasWithdrawalCount($user);
        $data['wait_withdrawal'] = $statisticsService->waitWithdrawalCount($user);
        //提现余额
        $data['rebate_balance'] = $data['rebate_count'] - $data['has_withdrawal'] - $data['wait_withdrawal'];
        return $data;
    }
}
