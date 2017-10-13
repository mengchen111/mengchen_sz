<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/12/17
 * Time: 12:17
 */

namespace App\Http\Controllers\Admin\Game;

use App\Services\Game\PlayerService;
use App\Services\Game\StatementDailyService;
use App\Services\Game\StatementMonthlyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class StatementController
{
    public function show()
    {
        //return Carbon::now()->toDateString() . ' ' . Carbon::now()->addWeek(1)->toDateString();
        //return StatementDailyService::getTotalPlayers();
        $statementDailyService = new StatementDailyService();
        $date = '2017-09-22';
        //return $statementDailyService->getRemainedData($date, Carbon::parse($date)->subDay(1)->toDateString());
        //return $statementDailyService->getCardBoughtData($date);
        //return $statementDailyService->getCardBoughtSum();
        //return StatementMonthlyService::getMonthlyCardBoughtSum('2017-09');
        //return StatementMonthlyService::getMonthlyCardBoughtPlayersSum('2017-09');
        return PlayerService::getAllPlayers();

    }
}