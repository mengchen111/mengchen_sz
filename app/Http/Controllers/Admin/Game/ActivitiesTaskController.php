<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use App\Services\Game\GameApiService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use App\Services\Paginator;
use Carbon\Carbon;
use App\Exceptions\CustomException;

class ActivitiesTaskController extends Controller
{
    public function getTaskList(AdminRequest $request)
    {
        $taskApi = config('custom.game_api_activities_task_list');
        $taskList = GameApiService::request('GET', $taskApi);
        krsort($taskList);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取任务列表', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($taskList, $this->per_page, $this->page);
    }

    public function editTask(AdminRequest $request)
    {
        $formData = $this->validateEditTaskForm($request);
        $formData['begin_time'] = Carbon::parse($formData['begin_time'])->timestamp;
        $formData['end_time'] = Carbon::parse($formData['end_time'])->timestamp;

        $api = config('custom.game_api_activities_task_modify');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '编辑任务', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '编辑成功',
        ];
    }

    protected function validateEditTaskForm($request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
            'name' => 'required|string|max:20',
            'type' => 'required|integer',       //任务类型,和task_type关联
            'begin_time' => 'required|date_format:"Y-m-d H:i:s',
            'end_time' => 'required|date_format:"Y-m-d H:i:s',
            'mission_time' => 'required|string',
            'target' => 'required|integer',     //任务次数
            'reward' => 'required|string',      //4_1奖励id（关联goods_type表）和奖励次数
            'daily' => 'required|integer|in:0,1',
            'link' => 'required|string',
            'count' => 'required|integer'
        ]);

        //检查结束时间应该大于开始时间
        $this->checkTime($request->input('begin_time'), $request->input('end_time'));

        return $request->only([
            'id', 'name', 'type', 'begin_time', 'end_time',
            'mission_time', 'target', 'reward', 'daily', 'link', 'count'
        ]);
    }

    protected function checkTime($startTime, $endTime)
    {
        $startTime = Carbon::parse($startTime);
        $endTime = Carbon::parse($endTime);
        if ($startTime->gte($endTime)) {    //开始时间不应该大于或等于结束时间
            throw new CustomException('结束时间应该大于开始时间');
        }
    }

    public function deleteTask(AdminRequest $request, $taskId)
    {
        $api = config('custom.game_api_activities_task_delete');
        GameApiService::request('POST', $api, ['id' => $taskId]);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除任务', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '删除成功',
        ];
    }

    public function addTask(AdminRequest $request)
    {
        $formData = $this->validateAddTaskForm($request);
        $formData['begin_time'] = Carbon::parse($formData['begin_time'])->timestamp;
        $formData['end_time'] = Carbon::parse($formData['end_time'])->timestamp;

        $api = config('custom.game_api_activities_task_add');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '添加任务', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '添加成功',
        ];
    }

    protected function validateAddTaskForm($request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:20',
            'type' => 'required|integer',       //任务类型,和task_type关联
            'begin_time' => 'required|date_format:"Y-m-d H:i:s',
            'end_time' => 'required|date_format:"Y-m-d H:i:s',
            'mission_time' => 'required|string',
            'target' => 'required|integer',     //任务次数
            'reward' => 'required|string',      //4_1奖励id（关联goods_type表）和奖励次数
            'daily' => 'required|integer|in:0,1',
            'link' => 'required|string',
            'count' => 'required|integer'
        ]);

        //检查结束时间应该大于开始时间
        $this->checkTime($request->input('begin_time'), $request->input('end_time'));

        return $request->only([
            'name', 'type', 'begin_time', 'end_time', 'mission_time',
            'target', 'reward', 'daily', 'link', 'count'
        ]);
    }

    public function getTaskTypeMap(AdminRequest $request)
    {
        $taskTypeApi = config('custom.game_api_activities_task-type_list');
        $taskType = GameApiService::request('GET', $taskTypeApi);
        $taskTypeMap = [];

        array_walk($taskType, function ($item) use (&$taskTypeMap) {
            $taskTypeMap[$item['id']] = $item['comment'];
        });

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取任务类型id和comment的映射表', $request->header('User-Agent'), json_encode($request->all()));

        return $taskTypeMap;
    }

    //获取任务id和任务name的map关系(前端options用到)
    public function getTaskMap(AdminRequest $request)
    {
        $taskApi = config('custom.game_api_activities_task_list');
        $taskList = GameApiService::request('GET', $taskApi);
        $taskMap = [];

        array_walk($taskList, function ($item) use (&$taskMap) {
            $taskMap[$item['id']] = $item['name'];
        });

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取任务id和name的映射表', $request->header('User-Agent'), json_encode($request->all()));

        return $taskMap;
    }
}
