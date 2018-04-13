<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WxTopUpRule extends Model
{
    protected $fillable = [
        'amount', 'give', 'first_give', 'price', 'remark'
    ];
    protected $appends = [
        'price_yuan',
    ];

    public function orders()
    {
        return $this->hasMany(WxOrder::class, 'wx_top_up_rule_id');
    }

    public function setPriceAttribute($price)
    {
        $this->attributes['price'] = $price * 100;
    }

    public function getPriceYuanAttribute()
    {
        return $this->attributes['price'] / 100;
    }
}
