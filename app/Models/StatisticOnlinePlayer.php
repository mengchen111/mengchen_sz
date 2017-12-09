<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatisticOnlinePlayer extends Model
{
    protected $table = 'statistic_online_player';
    protected $primaryKey = 'id';

    protected $guarded = [
        //
    ];

    protected $hidden = [
        'updated_at',
    ];
}
