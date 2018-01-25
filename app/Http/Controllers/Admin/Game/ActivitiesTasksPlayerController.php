<?php

namespace App\Http\Controllers\Admin\Game;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use App\Http\Requests\AdminRequest;
use App\Models\OperationLogs;
use App\Services\Paginator;

class ActivitiesTasksPlayerController extends Controller
{
    public function getTasksPlayerList(AdminRequest $request)
    {
        $tasksPlayerApi = config('custom.game_api_activities_tasks-player_list');
        $tasksPlayerList = GameApiService::request('GET', $tasksPlayerApi);
        krsort($tasksPlayerList);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取玩家任务列表', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($tasksPlayerList, $this->per_page, $this->page);
    }

    public function editTasksPlayer(AdminRequest $request)
    {
        $formData = $this->validateEditTasksPlayerForm($request);

        $api = config('custom.game_api_activities_tasks-player_modify');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '编辑玩家任务', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '编辑成功',
        ];
    }

    protected function validateEditTasksPlayerForm($request)
    {
        $this->validate($request, [
            'uid' => 'required|integer',
            'task_id' => 'required|integer',
            'process' => 'required|integer',
            'is_completed' => 'required|integer|in:0,1',
        ]);

        return $request->only([
            'uid', 'task_id', 'process', 'is_completed',
        ]);
    }

    public function deleteTasksPlayer(AdminRequest $request)
    {
        $formData = $request->only(['uid', 'task_id']);
        $api = config('custom.game_api_activities_tasks-player_delete');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除玩家任务', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '删除成功',
        ];
    }

    public function addTasksPlayer(AdminRequest $request)
    {
        $formData = $this->validateAddTasksPlayerForm($request);

        $api = config('custom.game_api_activities_tasks-player_add');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '添加玩家任务', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '添加成功',
        ];
    }

    protected function validateAddTasksPlayerForm($request)
    {
        $this->validate($request, [
            'uid' => 'required|integer',
            'task_id' => 'required|integer',
            'process' => 'required|integer',
            'is_completed' => 'required|integer|in:0,1',
        ]);

        return $request->only([
            'uid', 'task_id', 'process', 'is_completed',
        ]);
    }
}
