<?php


namespace App\Services;

use App\Models\CommunityList;
use App\Models\CommunityConf;

class CommunityService
{
    //玩家加入的社区数
    public static function playerJoinedCommunitiesCount($playerId)
    {
        $count = 0;
        $communities = CommunityList::where('status', 1)
            ->get()
            ->each(function ($item) {
                $item->append('member_ids');
            });
        foreach ($communities as $community) {
            if (in_array($playerId, $community->member_ids)) {
                $count += 1;
            }
        }
        return $count;
    }

    //玩家拥有的社区数量
    public static function playerOwnedCommunitiesCount($playerId)
    {
        return CommunityList::where('owner_player_id', $playerId)
            ->where('status', 1)    //审核已通过的社区
            ->count();
    }

    //玩家所在的社区总数量(加入的和拥有的)
    public static function playerInvolvedCommunitiesTotalCount($playerId)
    {
        return self::playerOwnedCommunitiesCount($playerId)
            + self::playerJoinedCommunitiesCount($playerId);
    }

    public static function getCommunityConf($communityId = 0)
    {
        $communityConf = CommunityConf::where('community_id', $communityId)->first();
        if (empty($communityConf)) {
            $communityConf = CommunityConf::where('community_id', 0)->firstOrFail();
        }
        return $communityConf;
    }

    //生成随机5位数的社团id
    public static function getRandomId()
    {
        $existsIds = CommunityList::all()->pluck('id')->toArray();
        $searchId = function () use ($existsIds, &$searchId) {
            $id = mt_rand(10000, 99999);
            if (in_array($id, $existsIds)) {
                $searchId();
            } else {
                return $id;
            }
        };
        return $searchId();
    }
}