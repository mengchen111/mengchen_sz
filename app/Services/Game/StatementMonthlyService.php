<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/13/17
 * Time: 12:12
 */

namespace App\Services\Game;

use App\Models\TopUpPlayer;
use Carbon\Carbon;

class StatementMonthlyService
{
    protected static $carType = 1;

    /**
     * 根据日期获取当月累计充卡的总量，默认查本月
     *
     * @param $date '格式2017-01'
     * @return mixed
     */
    public static function getMonthlyCardBoughtSum($date = 'today')
    {
        $date = Carbon::parse($date)->format('Y-m');
        list($year, $month) = explode('-', $date);
        return TopUpPlayer::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('type', self::$carType)
            ->get()
            ->sum('amount');
    }

    /**
     * 根据日期获取当月有过充卡记录的总玩家数，默认查本月
     *
     * @param $date '格式2017-01'
     * @return int
     */
    public static function getMonthlyCardBoughtPlayersSum($date = 'today')
    {
        $date = Carbon::parse($date)->format('Y-m');
        list($year, $month) = explode('-', $date);
        return TopUpPlayer::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('type', self::$carType)
            ->get()
            ->groupBy('player')
            ->count();
    }
}