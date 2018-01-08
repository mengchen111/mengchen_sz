<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use App\Models\OperationLogs;
use App\Services\Paginator;

class ActivitiesRewardController extends Controller
{
    public function getActivitiesRewardMap(AdminRequest $request)
    {
        $api = config('custom.game_api_activities_activities-reward');
        $reward = GameApiService::request('GET', $api);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查询活动奖品id和奖品名映射关系', $request->header('User-Agent'), json_encode($request->all()));

        $rewardMap = [];
        array_walk($reward, function ($value) use (&$rewardMap) {
            $rewardMap[$value['pid']] = $value['name'];
        });
        return $rewardMap;
    }
}
