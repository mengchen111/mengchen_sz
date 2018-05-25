<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @SWG\Definition(
 *   definition="CommunityInvitationApplication",
 *   description="牌艺馆邀请/申请记录模型",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="id",
 *       type="integer",
 *       format="int32",
 *       example=1,
 *   ),
 *   @SWG\Property(
 *       property="player_id",
 *       description="玩家id",
 *       type="integer",
 *       format="int32",
 *       example=10000,
 *   ),
 *   @SWG\Property(
 *       property="type",
 *       description="类型(0-申请,1-邀请)",
 *       type="integer",
 *       format="int32",
 *       example=0,
 *   ),
 *   @SWG\Property(
 *       property="community_id",
 *       description="牌艺馆id",
 *       type="integer",
 *       format="int32",
 *       example=10000,
 *   ),
 *   @SWG\Property(
 *       property="status",
 *       description="状态(0-pending,1-approved,2-declined)",
 *       type="integer",
 *       example=0,
 *   ),
 *   allOf={
 *       @SWG\Schema(ref="#/definitions/CreatedAtUpdatedAt"),
 *   }
 * )
 *
 */
class CommunityInvitationApplication extends Model
{
    public $timestamps = true;
    protected $table = 'community_invitation_application';
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
}
