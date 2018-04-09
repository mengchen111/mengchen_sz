<?php

namespace App\Models;

use App\Traits\WeChatPaymentTrait;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WxOrder extends Model
{
    use WeChatPaymentTrait;

    protected $fillable = [
        'user_id', 'wx_top_up_rule_id', 'out_trade_no', 'total_fee', 'body', 'spbill_create_ip',
        'order_status', 'order_err_msg', 'prepay_id', 'code_url', 'open_id', 'paid_at', 'is_first_order'
    ];
    protected $appends = [
        'total_fee_yuan',
        'order_status_name',
        'item_delivery_status_name'
    ];

    public function rule()
    {
        return $this->belongsTo(WxTopUpRule::class, 'wx_top_up_rule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOrderQrCodeAttribute()
    {
        $url = $this->attributes['code_url'];
        if (empty($url)) {
            return null;
        }
        return base64_encode(QrCode::format('png')->size(200)->generate($url));
    }

    public function getTotalFeeYuanAttribute()
    {
        return $this->attributes['total_fee'] / 100;
    }

    public function getOrderStatusNameAttribute()
    {
        return $this->orderStatusMap[$this->attributes['order_status']];
    }

    public function getItemDeliveryStatusNameAttribute()
    {
        return $this->itemDeliveryStatusMap[$this->attributes['item_delivery_status']];
    }

    public function scopeFinishedOrder($query)
    {
        return $query->where('order_status',2)->where('item_delivery_status',1)->whereNotNull('paid_at');
    }
}
