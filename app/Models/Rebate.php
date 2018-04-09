<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rebate extends Model
{
    protected $fillable = [
        'user_id','children_id','total_amount','rebate_at','rebate_price','rebate_rule_id','remark'
    ];
    protected $dates = ['rebate_at'];
}
