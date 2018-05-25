<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @SWG\Definition(
 *   definition="TopUpPlayer",
 *   description="玩家充值记录模型",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="充值记录id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="provider_id",
 *       description="发起充值的用户id(代理商id)",
 *       type="integer",
 *       format="int32",
 *       example=540,
 *   ),
 *   @SWG\Property(
 *       property="player",
 *       description="玩家id",
 *       type="integer",
 *       format="int32",
 *       example=10000,
 *   ),
 *   @SWG\Property(
 *       property="type",
 *       description="道具类型id",
 *       type="string",
 *       example="1",
 *   ),
 *   @SWG\Property(
 *       property="amount",
 *       description="道具数量",
 *       type="integer",
 *       format="int32",
 *       example=10,
 *   ),
 *   allOf={
 *       @SWG\Schema(ref="#/definitions/CreatedAtUpdatedAt"),
 *   }
 * )
 *
 */
class TopUpPlayer extends Model
{
    protected $table = 'top_up_player';
    protected $primaryKey = 'id';

    protected $fillable = [
        'provider_id', 'player', 'type', 'amount', 'created_at'
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function provider()
    {
        return $this->hasOne('App\Models\User', 'id', 'provider_id');
    }

    public function item()
    {
        return $this->hasOne('App\Models\ItemType', 'id', 'type');
    }
}