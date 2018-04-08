<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WxTopUpRule extends Model
{
    protected $fillable = [
        'amount', 'give', 'first_give', 'price', 'remark'
    ];

    public function orders()
    {
        return $this->hasMany(WxOrder::class, 'wx_top_up_rule_id');
    }
}
