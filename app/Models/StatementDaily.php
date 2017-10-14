<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StatementDaily extends Model
{
    protected $table = 'statement_daily';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $dateFormat = 'Y-m-d';

    protected $fillable = [
        'date', 'peak_online_players', 'active_players', 'incremental_players', 'one_day_remained',
        'one_week_remained', 'two_weeks_remained', 'one_month_remained', 'card_consumed_data',
        'card_bought_data', 'card_consumed_sum', 'card_bought_sum', 'players_data'
    ];

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format($this->dateFormat);
    }
}
