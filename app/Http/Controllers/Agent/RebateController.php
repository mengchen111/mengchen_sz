<?php

namespace App\Http\Controllers\Agent;

use App\Http\Requests\AgentRequest;
use App\Services\WithdrawalStatisticsService;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class RebateController extends Controller
{
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
        $rebates = $rebates->paginate($this->per_page);
        return $rebates;
    }

    public function statistics(AgentRequest $request,WithdrawalStatisticsService $statisticsService)
    {
        $user = auth()->user();
        $data['rebate_count'] = $statisticsService->rebateCount($user);
        $data['has_withdrawal'] = $statisticsService->hasWithdrawalCount($user);
        $data['wait_withdrawal'] = $statisticsService->waitWithdrawalCount($user);
        //提现余额
        $data['rebate_balance'] = $data['rebate_count'] - $data['has_withdrawal'] - $data['wait_withdrawal'];
        return $data;
    }


}
