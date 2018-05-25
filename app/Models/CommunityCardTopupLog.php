<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @SWG\Definition(
 *   definition="CommunityCardTopUpLog",
 *   description="牌艺馆充值记录模型",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="充值记录id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="community_id",
 *       description="牌艺馆id",
 *       type="integer",
 *       format="int32",
 *       example=10000,
 *   ),
 *   @SWG\Property(
 *       property="agent_id",
 *       description="发起充值的代理商id",
 *       type="integer",
 *       format="int32",
 *       example=540,
 *   ),
 *   @SWG\Property(
 *       property="item_type_id",
 *       description="道具类型id",
 *       type="integer",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="item_amount",
 *       description="道具数量",
 *       type="integer",
 *       format="int32",
 *       example=10,
 *   ),
 *   @SWG\Property(
 *       property="remark",
 *       description="备注",
 *       type="string",
 *       example="remark",
 *   ),
 *   @SWG\Property(
 *       property="created_at",
 *       description="创建时间",
 *       type="string",
 *       example="2018-05-24 17:29:59",
 *   ),
 * )
 *
 */
class CommunityCardTopupLog extends Model
{
    public $timestamps = false;
    protected $table = 'community_card_topup_log';
    protected $primaryKey = 'id';

    protected $guarded = [
        //
    ];

    protected $hidden = [
        //
    ];

    public function community()
    {
        return $this->hasOne('App\Models\CommunityList', 'id', 'community_id');
    }

    public function item()
    {
        return $this->hasOne('App\Models\ItemType', 'id', 'item_type_id');
    }
}
