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
use App\Services\Game\StatementDailyService;
use App\Services\Game\StatementMonthlyService;
use Illuminate\Http\Request;

class StatementSummaryController
{
    protected $data = [
        'total_players_amount' => 0,
        'online_players_amount' => 0,
        'peak_online_amount' => 0,
        'active_players_amount' => 0,
        'incremental_amount' => 0,
        'one_day_remained' => 0,
        'one_week_remained' => 0,
        'two_weeks_remained' => 0,
        'one_month_remained' => 0,
        'card_consumed_amount' => 0,
        'card_average_consumed' => 0,
        'card_bought_amount' => 0,
        'card_average_bought' => 0,
        'card_bought_sum' => 0,
        'card_consumed_sum' => 0,
        'monthly_card_bought_players' => 0,
        'monthly_card_bought_sum' => 0,
    ];

    public function show(AdminRequest $request)
    {
        $statementDailyService = new StatementDailyService();

        try {
            //TODO
        } catch (\Exception $exception) {
            throw new CustomException($exception->getMessage());
        }
    }
}