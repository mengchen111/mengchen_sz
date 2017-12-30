<?php
/**
 * 有效耗卡规则：
 * 代理商1（记作A1）给玩家1（记作P1）充值了10张卡，代理商2（记作A2）给玩家P1充值了20张卡
    a, 充值完成之后，此时A1的有效耗卡为0（因为玩家没消耗这些卡）
    b, P1在游戏过程中消耗了5张卡，此时A1的有效耗卡为5，A2的有效耗卡为0（P1消耗了5张卡，没有超过A1充值的10张卡，所以A2的有效耗卡为0）
    c，P1在游戏过程中在b情况的基础上又效耗了6张卡，此时A1的有效耗卡为10，A2的有效耗卡为1（P1的总消耗卡数为11，减去A1充值的10，所以A1有效耗卡为10，A2有效耗卡为(11-10=1)张）
 * 玩家充值记录表(top_up_player)：小于0的不加入计算(即给玩家减房卡的记录不计算)。
 */

namespace App\Services\Game;

use App\Models\TopUpPlayer;
use Illuminate\Support\Facades\Cache;

class ValidCardConsumedService
{
    public static function getCurrencyLog()
    {
        $cardConsumedLogApi = config('custom.game_api_currency_log');
        $cacheKey = config('custom.game_server_cache_currency_log');
        $cacheDuration = 1;     //缓存1分钟

        //获取道具消耗记录并缓存一分钟
        return Cache::remember($cacheKey, $cacheDuration, function () use ($cardConsumedLogApi) {
            return GameApiService::request('GET', $cardConsumedLogApi);
        });
    }

    public static function getCardConsumedLog()
    {
        $currencyLog = self::getCurrencyLog();
        return collect($currencyLog)->where('type', 1)  //房卡道具类型id
            ->where('val', '<', 0);                     //val大于0为代理商给玩家的充卡记录，小于0的为玩家的耗卡记录
    }

    //获取玩家房卡的充值和消费流
    public static function getPlayerCardConsumedFlow()
    {
        //获取充值记录，排序默认是id升序(即最先充值的排前面)，不用调整
        $playerTopUpData = TopUpPlayer::where('amount', '>', 0)->get()->groupBy('player');
        $PlayerCardConsumedFlowData = [];
        foreach ($playerTopUpData as $playerId => &$topUpLogs) {
            //玩家总的耗卡量，如果where查找不到玩家，会返回0
            $playerCardConsumedTotalNum = abs(self::getCardConsumedLog()->where('uid', $playerId)->sum('val'));
            //每条充值记录中增加一个有效耗卡字段
            $topUpLogs = self::addValidCardConsumedNum($topUpLogs, $playerCardConsumedTotalNum);

            $PlayerCardConsumedFlowData[$playerId] = [
                'top_up_logs' => $topUpLogs,
                'card_consumed_total_num' => $playerCardConsumedTotalNum,
            ];
        }
        return $PlayerCardConsumedFlowData;
    }

    protected static function addValidCardConsumedNum($topUpLogs, $totalCardConsumedNum)
    {
        foreach ($topUpLogs as &$topUpLog) {
            if ($totalCardConsumedNum >= $topUpLog['amount']) {
                $topUpLog['valid_card_consumed_num'] = $topUpLog['amount'];
                $totalCardConsumedNum -= $topUpLog['amount'];
            } else {
                if ($totalCardConsumedNum > 0) {
                    $topUpLog['valid_card_consumed_num'] = $totalCardConsumedNum;
                    $totalCardConsumedNum -= $topUpLog['amount'];
                } else {
                    $topUpLog['valid_card_consumed_num'] = 0;
                }
            }
        }
        return $topUpLogs;
    }

    //获取包含了有效耗卡数据的代理商给玩家充值记录
    public static function getAgentTopUpLogs()
    {
        $playerCardConsumedFlow = self::getPlayerCardConsumedFlow();
        return collect($playerCardConsumedFlow)
            ->pluck('top_up_logs')
            ->flatten()
            ->sortByDesc('id')  //充值记录时间倒序排序
            ->groupBy('provider_id');
    }

    //获取指定的代理商的充值记录(with 有效耗卡字段)
    public static function getSpecifiedAgentTopUpLog($agentId)
    {
        $agentTopUpLogs = self::getAgentTopUpLogs();
        return $agentTopUpLogs->has($agentId)
            ? $agentTopUpLogs[$agentId]
            : [];
    }

    //获取指定的agent id的代理商的有效耗卡数
    public static function getAgentValidCardConsumedNum($agentId)
    {
        $agentTopUpLogs = self::getAgentTopUpLogs();
        return $agentTopUpLogs->has($agentId)
            ? $agentTopUpLogs[$agentId]->sum('valid_card_consumed_num')
            : 0;
    }
}