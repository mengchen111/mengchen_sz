<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WxOrder extends Model
{
    protected $fillable = [
        'user_id', 'wx_top_up_rule_id', 'out_trade_no', 'total_fee', 'body', 'spbill_create_ip',
        'order_status', 'order_err_msg', 'prepay_id', 'code_url', 'open_id', 'paid_at', 'is_first_order'
    ];

    public function rule()
    {
        return $this->belongsTo(WxTopUpRule::class, 'wx_top_up_rule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
