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
        $api = config('custom.game_api_activities_activities-list');
        $records = GameApiService::request('GET', $api);
        krsort($records);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查询活动列表', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($records, $this->per_page, $this->page);
    }

    public function editActivitiesList(AdminRequest $request)
    {
        $this->validateEditActivitiesForm($request);

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
        ]);

        //检查结束时间应该大于开始时间
        $this->checkTime($request->input('open_time'), $request->input('end_time'));
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
    }

    public function checkTime($startTime, $endTime)
    {
        $startTime = Carbon::parse($startTime);
        $endTime = Carbon::parse($endTime);
        if ($startTime->gte($endTime)) {    //开始时间不应该大于或等于结束时间
            throw new CustomException('结束时间应该大于开始时间');
        }
    }

    public function deleteActivitiesList(AdminRequest $request, $aid)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除活动列表', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'data' => $aid,
            'message' => '删除成功',
        ];
    }

    public function addActivitiesList(AdminRequest $request)
    {
        $this->validateAddActivitiesForm($request);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '添加活动列表', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'data' => $request->all(),
            'message' => '添加成功',
        ];
    }
}
