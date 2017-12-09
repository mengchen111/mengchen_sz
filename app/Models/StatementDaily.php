<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StatementDaily extends Model
{
    protected $table = 'statement_daily';
    protected $primaryKey = 'id';
    //protected $dateFormat = 'Y-m-d';

    protected $guarded = [
        //
    ];

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }
}