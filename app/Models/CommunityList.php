<?php

namespace App\Models;

use App\Services\Game\PlayerService;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\Array_;

class CommunityList extends Model
{
    public $timestamps = true;
    protected $table = 'community_list';
    protected $primaryKey = 'id';

    protected $guarded = [
        //
    ];

    protected $hidden = [
        //
    ];

    protected $appends = [
        'members_count',
    ];

    public function ownerAgent()
    {
        return $this->hasOne('App\Models\User', 'id', 'owner_agent_id');
    }

    public function getMembersCountAttribute()
    {
        $members = $this->attributes['members'];
        return empty($members) ? 0 : count(explode(',', $members));
    }

    //将成员信息结构，然后获取成员的基本信息(头像，昵称和id)
    public function getMembersInfoAttribute()
    {
        $remainedPlayerInfo = ['id', 'nickname', 'headimg'];    //只显示这些玩家信息

        $returnData = [];
        $members = $this->attributes['members'];
        if (empty($members)) {
            return $returnData;
        }
        $players = PlayerService::batchFindPlayer($members);
        foreach ($players as $player) {
            $player = collect($player)->only($remainedPlayerInfo)->toArray();
            array_push($returnData, $player);
        }
        return $returnData;
    }

    public function addMembers(Array $newMembers)
    {
        $existMembers = explode(',', $this->members);
        foreach ($newMembers as $newMember) {
            if (!in_array($newMembers, $existMembers)) {
                array_push($existMembers, $newMember);
            }
        }
        $this->members = implode(',', $existMembers);
        $this->save();
    }

    public function deleteMembers(Array $abandonedMembers)
    {
        $existMembers = explode(',', $this->members);
        foreach ($abandonedMembers as $abandonedMember) {
            if (in_array($abandonedMember, $existMembers)) {
                unset($existMembers[array_search($abandonedMember, $existMembers)]);
            }
        }
        $this->members = implode(',', $existMembers);
        $this->save();
    }

    //检查成员是否存在此群中
    public function ifHasMember($playerId)
    {
        $existMembers = explode(',', $this->members);
        return in_array($playerId, $existMembers);
    }
}
