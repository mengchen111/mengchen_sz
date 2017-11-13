<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/14/17
 * Time: 09:35
 */

namespace App\Http\Controllers\Admin\Game;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use App\Models\StatementDaily;
use App\Services\Game\StatementDailyService;
use App\Services\Game\StatementMonthlyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\OperationLogs;

class StatementSummaryController
{
    protected $data = [
        'peak_online_players' => 0,         //日高峰
        'active_players' => 0,              //当日活跃用户
        'incremental_players' => 0,         //新增玩家数
        'one_day_remained' => '0|0|0.00',   //次日留存, 留存玩家数|创建日新增玩家数|百分比(保留两位小数)
        'one_week_remained' => '0|0|0.00',  //7日留存
        'two_weeks_remained' => '0|0|0.00', //14日留存
        'one_month_remained' => '0|0|0.00', //30日留存
        'card_consumed_data' => '0|0|0',    //当日耗卡数|当日有过耗卡记录的玩家总数|平均耗卡数(向上取整的比值)
        'card_bought_data' => '0|0|0',      //当日玩家购卡总数|当日有过购卡记录的玩家总数|平均购卡数(向上取整的比值)
        'card_consumed_sum' => 0,           //截止当日玩家耗卡总数
        'card_bought_sum' => 0,             //截止当日给玩家充卡总数
        'monthly_card_bought_players' => 0, //当月累计充卡玩家数
        'monthly_card_bought_sum' => 0,     //当月累计给玩家充卡总数
    ];

    protected $realTimeData = [
        'total_players_amount' => 0,        //累计用户
        'online_players_amount' => 0,       //在线人数
    ];

    public function show(AdminRequest $request)
    {
        $date = $request->date ?: Carbon::parse($request->date)->toDateString();

        //月数据
        $this->data['monthly_card_bought_players'] = StatementMonthlyService::getMonthlyCardBoughtPlayersSum($date);
        $this->data['monthly_card_bought_sum'] = StatementMonthlyService::getMonthlyCardBoughtSum($date);

        //如果时间为今天，查询实时数据
        if (Carbon::parse($date)->isToday()) {
            $statementDailyService = new StatementDailyService();

            $this->data['peak_online_players'] = $statementDailyService->getPeakOnlinePlayersAmount($date);
            $this->data['active_players'] = $statementDailyService->getActivePlayersAmount($date);
            $this->data['incremental_players'] = $statementDailyService->getIncrementalPlayersAmount($date);
            $this->data['one_day_remained'] = $statementDailyService->getRemainedData($date, 1);
            $this->data['one_week_remained'] = $statementDailyService->getRemainedData($date, 7);
            $this->data['two_weeks_remained'] = $statementDailyService->getRemainedData($date, 14);
            $this->data['one_month_remained'] = $statementDailyService->getRemainedData($date, 30);
            $this->data['card_consumed_data'] = $statementDailyService->getCardConsumedData($date);
            $this->data['card_bought_data'] = $statementDailyService->getCardBoughtData($date);
            $this->data['card_consumed_sum'] = $statementDailyService->getCardConsumedSum($date);
            $this->data['card_bought_sum'] = $statementDailyService->getCardBoughtSum($date);
        } else {
            //如果时间为历史时间，则从数据库中取数据
            $statement = StatementDaily::whereDate('date', $date)->first();
            if (empty($statement)) {
                throw new CustomException("${date}: 无此日期的数据");
            }
            $this->data = array_merge($this->data, $statement->toArray());
            unset($this->data['players_data']);     //玩家数据不返回
        }

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看报表数据总览', $request->header('User-Agent'), json_encode($request->all()));

        return $this->data;
    }

    public function showRealTimeData(AdminRequest $request)
    {
        //实时数据
        $statementDailyService = new StatementDailyService();
        $this->realTimeData['total_players_amount'] = $statementDailyService->getTotalPlayersAmount();
        $this->realTimeData['online_players_amount'] = $statementDailyService->getOnlinePlayersAmount();

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看实时报表数据', $request->header('User-Agent'), json_encode($request->all()));

        return $this->realTimeData;
    }
}