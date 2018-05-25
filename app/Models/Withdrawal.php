<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @SWG\Definition(
 *   definition="Withdrawal",
 *   description="提现申请模型",
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
 *       property="amount",
 *       description="申请提现数量",
 *       type="integer",
 *       format="int32",
 *       example=200,
 *   ),
 *   @SWG\Property(
 *       property="wechat",
 *       description="微信联系方式",
 *       type="string",
 *       example="wechat",
 *   ),
 *   @SWG\Property(
 *       property="phone",
 *       description="手机联系方式",
 *       type="string",
 *       example="18888888888"
 *   ),
 *   @SWG\Property(
 *       property="status",
 *       description="状态(0-待审核,1-待发放,2-已发放,3-审核拒绝)",
 *       type="integer",
 *       example=0,
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
class Withdrawal extends Model
{
    protected $fillable = [
        'user_id','amount','wechat','phone','status','remark'
    ];
    protected $appends = [
        'withdrawal_status'
    ];
    public $status = [
        '待审核','待发放','已发放','审核拒绝',
    ];

    public function getWithdrawalStatusAttribute()
    {
        return $this->status[$this->attributes['status']];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
