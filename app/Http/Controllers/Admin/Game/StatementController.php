<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/12/17
 * Time: 12:17
 */

namespace App\Http\Controllers\Admin\Game;

use App\Services\Game\StatementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class StatementController
{
    public function show()
    {
        //return Carbon::now()->toDateString() . ' ' . Carbon::now()->addWeek(1)->toDateString();
        //return StatementService::getTotalPlayers();
        $statementService = new StatementService();
        $date = '2017-09-26';
        //return $statementService->getRemainedData($date, Carbon::parse($date)->subDay(1)->toDateString());
        return $statementService->getCardBoughtPlayers($date);
    }
}