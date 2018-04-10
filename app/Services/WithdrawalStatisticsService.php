<?php
/**
 * Created by PhpStorm.
 * User: wangjun
 * Date: 2018/4/9
 * Time: 17:47
 */

namespace App\Services;

use App\Models\User;

class WithdrawalStatisticsService
{
    /**
     *  累计返利总额
     * @param User $user
     * @return mixed
     */
    public function rebateCount(User $user)
    {
        return $user->rebates->sum('rebate_price');
    }

    /**
     * 已提现总额
     * @param User $user
     * @return mixed
     */
    public function hasWithdrawalCount(User $user)
    {
        return $user->withdrawals()->where('status',2)->get()->sum('amount');
    }

    /**
     * 等待提现总额
     * @param User $user
     * @return mixed
     */
    public function waitWithdrawalCount(User $user)
    {
        return $user->withdrawals()->whereIn('status',[0,1])->get()->sum('amount');
    }

}