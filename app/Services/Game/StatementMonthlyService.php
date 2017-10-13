<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/13/17
 * Time: 12:12
 */

namespace App\Services\Game;

use App\Models\TopUpPlayer;

class StatementMonthlyService
{
    protected static $carType = 1;

    //根据日期获取当月累计充卡的总量
    public static function getMonthlyCardBoughtSum($date)
    {
        list($year, $month) = explode('-', $date);
        return TopUpPlayer::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('type', self::$carType)
            ->get()
            ->sum('amount');
    }

    //根据日期获取当月有过充卡记录的总玩家数
    public static function getMonthlyCardBoughtPlayersSum($date)
    {
        list($year, $month) = explode('-', $date);
        return TopUpPlayer::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('type', self::$carType)
            ->get()
            ->groupBy('player')
            ->count();
    }
}