<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use App\Models\OperationLogs;

class ActivitiesStatementController extends Controller
{
    public function getStatement(AdminRequest $request)
    {
        $this->validate($request, [
            'date' => 'required|date_format:"Y-m-d"',
        ]);
        $params = $request->only('date');

        $rewardLogApi = config('custom.game_api_activities_reward_log');
        $rewardLog = GameApiService::request('GET', $rewardLogApi, $params);
        $taskLog = [];

        $result = [
            'task' => $taskLog,
            'reward' => $rewardLog,
        ];

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取数据统计日志', $request->header('User-Agent'), json_encode($request->all()));

        return $result;
    }
}
