<?php

namespace App\Http\Controllers\Admin\Game;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use App\Services\Paginator;
use App\Models\OperationLogs;

class ActivitiesController extends Controller
{
    public function getActivitiesList(AdminRequest $request)
    {
        $api = config('custom.game_api_activities_list');
        $activities = GameApiService::request('GET', $api);
        krsort($activities);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取活动列表', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($activities, $this->per_page, $this->page);
    }

    public function editActivitiesList(AdminRequest $request)
    {
        $formData = $this->validateEditActivitiesForm($request);
        $formData['open_time'] = Carbon::parse($formData['open_time'])->timestamp;
        $formData['end_time'] = Carbon::parse($formData['end_time'])->timestamp;

        $api = config('custom.game_api_activities_modify');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '编辑活动列表', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'data' => $request->all(),
            'message' => '编辑成功',
        ];
    }

    protected function validateAddActivitiesForm($request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',                    //活动名字
            'open' => 'required|in:0,1',    //开启状态
            'open_time' => 'required|date_format:"Y-m-d H:i:s"',    //开始时间
            'end_time' => 'required|date_format:"Y-m-d H:i:s"',     //开始时间
            'reward' => 'required|string',                          //奖品
            'reward_refresh_time' => 'required|string',
        ]);

        //检查结束时间应该大于开始时间
        $this->checkTime($request->input('open_time'), $request->input('end_time'));

        return $request->only([
            'name', 'open', 'open_time', 'end_time', 'reward', 'reward_refresh_time',
        ]);
    }

    protected function validateEditActivitiesForm($request)
    {
        $this->validate($request, [
            'aid' => 'required|integer',    //活动id
            'name' => 'required|string|max:255',                    //活动名字
            'open' => 'required|in:0,1',    //开启状态
            'open_time' => 'required|date_format:"Y-m-d H:i:s"',    //开始时间
            'end_time' => 'required|date_format:"Y-m-d H:i:s"',     //开始时间
            'reward' => 'required|string',                          //奖品
        ]);

        //检查结束时间应该大于开始时间
        $this->checkTime($request->input('open_time'), $request->input('end_time'));

        return $request->only([
            'aid', 'name', 'open', 'open_time', 'end_time', 'reward', 'reward_refresh_time',
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

    public function deleteActivitiesList(AdminRequest $request, $aid)
    {
        $api = config('custom.game_api_activities_delete');
        GameApiService::request('POST', $api, ['aid' => $aid]);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除活动列表', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '删除成功',
        ];
    }

    public function addActivitiesList(AdminRequest $request)
    {
        $formData = $this->validateAddActivitiesForm($request);
        $formData['open_time'] = Carbon::parse($formData['open_time'])->timestamp;
        $formData['end_time'] = Carbon::parse($formData['end_time'])->timestamp;

        $api = config('custom.game_api_activities_add');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '添加活动列表', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '添加成功',
        ];
    }
}
