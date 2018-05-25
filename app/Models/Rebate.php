<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 *
 * @SWG\Definition(
 *   definition="Rebate",
 *   description="返利信息模型",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="user_id",
 *       description="代理商id",
 *       type="integer",
 *       format="int32",
 *       example=540,
 *   ),
 *   @SWG\Property(
 *       property="children_id",
 *       description="下级代理商id",
 *       type="integer",
 *       format="int32",
 *       example=200,
 *   ),
 *   @SWG\Property(
 *       property="total_amount",
 *       description="当月充值金额",
 *       type="integer",
 *       format="int32",
 *       example=1
 *   ),
 *   @SWG\Property(
 *       property="rebate_at",
 *       description="返利时间月份",
 *       type="string",
 *       example="2018-03"
 *   ),
 *   @SWG\Property(
 *       property="rebate_price",
 *       description="返利金额",
 *       type="string",
 *       example="228.00",
 *   ),
 *   @SWG\Property(
 *       property="rebate_rule_id",
 *       description="返利规则id",
 *       type="integer",
 *       format="int32",
 *       example=1
 *   ),
 *   @SWG\Property(
 *       property="remark",
 *       description="备注",
 *       type="string",
 *       example="remark",
 *   ),
 *   allOf={
 *       @SWG\Schema(ref="#/definitions/CreatedAtUpdatedAt"),
 *   }
 * )
 *
 */
class Rebate extends Model
{
    protected $fillable = [
        'user_id', 'children_id', 'total_amount', 'rebate_at', 'rebate_price', 'rebate_rule_id', 'remark'
    ];
    protected $appends = [
        'rebate_at'
    ];

    public function children()
    {
        return $this->belongsTo(User::class, 'children_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rule()
    {
        return $this->belongsTo(RebateRule::class, 'rebate_rule_id');
    }

    public function getRebateAtAttribute()
    {
        return Carbon::parse($this->attributes['rebate_at'])->format('Y-m');
    }
}
