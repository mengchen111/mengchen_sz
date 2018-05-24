<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @SWG\Definition(
 *   definition="WxTopUpRule",
 *   description="微信充值套餐模型",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="套餐id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="price",
 *       description="套餐价格(单位分)",
 *       type="integer",
 *       format="int32",
 *       example=8000,
 *   ),
 *   @SWG\Property(
 *       property="price_yuan",
 *       description="套餐价格(单位元)",
 *       type="integer",
 *       format="int32",
 *       example=80,
 *   ),
 *   @SWG\Property(
 *       property="remark",
 *       description="套餐备注",
 *       type="string",
 *       example="100张",
 *   ),
 * )
 *
 */
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
