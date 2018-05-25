<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @SWG\Definition(
 *   definition="CommunityMemberLog",
 *   description="牌艺馆成员动态日志模型",
 *   type="object",
 *   @SWG\Property(
 *       property="id",
 *       description="id",
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
 *       property="player_id",
 *       description="玩家id",
 *       type="integer",
 *       format="int32",
 *       example=10000,
 *   ),
 *   @SWG\Property(
 *       property="action",
 *       description="动作('加入'等)",
 *       type="string",
 *       example="加入",
 *   ),
 *   @SWG\Property(
 *       property="created_at",
 *       description="创建时间",
 *       type="string",
 *       example="2018-01-25 17:54:13",
 *   ),
 * )
 *
 */
class CommunityMemberLog extends Model
{
    public $timestamps = false;
    protected $table = 'community_member_log';
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
