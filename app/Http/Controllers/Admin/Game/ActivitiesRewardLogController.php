<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use App\Models\OperationLogs;

class ActivitiesRewardLogController extends Controller
{
    public function show(AdminRequest $request)
    {
        $params = $request->has('filter') ? ['uid' => $request->input('filter')] : [];
        $api = config('custom.game_api_activities_log-activity-reward');
        $logActivitiesReward = GameApiService::request('GET', $api, $params);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看用户奖品记录', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($logActivitiesReward, $this->per_page, $this->page);
    }
}
