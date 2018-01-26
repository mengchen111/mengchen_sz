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

    //获取此社区的申请列表
    public function getApplicationDataAttribute()
    {
        $applicationData = [];
        $applications = CommunityInvitationApplication::where('community_id', $this->attributes['id'])
            ->where('type', 0)  //类型为申请
            ->where('status', 0)    //状态为pending
            ->get();
        $applicationData['application_count'] = $applications->count(); //申请数量

        $remainedPlayerInfo = ['id', 'nickname', 'headimg'];    //只显示这些玩家信息
        foreach ($applications as &$application) {
            $player = PlayerService::findPlayer($application->player_id);
            $player = collect($player)->only($remainedPlayerInfo)->toArray();
            $application['player'] = $player;   //添加申请者的基本信息
        }
        $applicationData['applications'] = $applications;   //申请信息

        return $applicationData;
    }

    //社区动态列表
    public function getMemberLogAttribute()
    {
        $memberLogs = CommunityMemberLog::where('community_id', $this->attributes['id'])
            ->orderBy('id', 'desc')
            ->limit(10)     //只显示10条最新动态
            ->get();
        $remainedPlayerInfo = ['id', 'nickname'];    //只显示这些玩家信息
        foreach ($memberLogs as $memberLog) {
            $player = PlayerService::findPlayer($memberLog->player_id);
            $player = collect($player)->only($remainedPlayerInfo)->toArray();
            $memberLog['player'] = $player;
        }
        return $memberLogs;
    }

    //获取成员id的数组列表
    public function getMemberIdsAttribute()
    {
        return explode(',', $this->attributes['members']);
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
