<?php

namespace App\Http\Controllers\Admin\Game;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use App\Models\OperationLogs;
use App\Services\Paginator;

class ActivitiesRewardController extends Controller
{
    protected $activitiesRewardApi;

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->activitiesRewardApi = config('custom.game_api_activities_reward_list');
    }

    public function getActivitiesRewardMap(AdminRequest $request)
    {
        $reward = GameApiService::request('GET', $this->activitiesRewardApi);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取活动奖品id和奖品名映射关系', $request->header('User-Agent'), json_encode($request->all()));

        $rewardMap = [];
        array_walk($reward, function ($value) use (&$rewardMap) {
            $rewardMap[$value['pid']] = $value['show_text'];
        });
        return $rewardMap;
    }

    public function getActivitiesRewardList(AdminRequest $request)
    {
        $reward = GameApiService::request('GET', $this->activitiesRewardApi);
        krsort($reward);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取活动奖品列表', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($reward, $this->per_page, $this->page);
    }

    public function editReward(AdminRequest $request)
    {
        $formData = $this->validateEditRewardForm($request);

        $api = config('custom.game_api_activities_reward_modify');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '编辑活动奖品', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '编辑成功',
        ];
    }

    protected function validateEditRewardForm($request)
    {
        $this->validate($request, [
            'pid' => 'required|integer',
            'name' => 'required|string|max:45',
            'img' => 'required|string|max:45',
            'show_text' => 'required|string|max:45',
            'total_inventory' => 'required|integer',
            'probability' => 'required|integer',
            'single_limit' => 'required|integer',
            'expend' => 'required|integer',
            'goods_type' => 'required|integer',
            'goods_count' => 'required|numeric',
            'whitelist' => 'nullable|string',
            'begin_time' => 'required|required_with_all:end_time|date_format:"Y-m-d H:i:s"',
            'end_time' => 'required|required_with_all:begin_time|date_format:"Y-m-d H:i:s"',
        ]);

        $data = $request->only([
            'pid', 'name', 'img', 'show_text', 'total_inventory', 'probability',
            'single_limit', 'expend', 'goods_type', 'goods_count', 'whitelist',
            'begin_time', 'end_time'
        ]);
        if (!$data['whitelist']) {  //不然调用api接口报错
            unset($data['whitelist']);
        }
        //转换为时间戳形式
        $data['begin_time'] = Carbon::parse($data['begin_time'])->timestamp;
        $data['end_time'] = Carbon::parse($data['end_time'])->timestamp;
        return $data;
    }

    public function deleteReward(AdminRequest $request, $pid)
    {
        //查看此奖励是否有被活动使用
        $this->checkIfRewardInUse($pid);

        $api = config('custom.game_api_activities_reward_delete');
        GameApiService::request('POST', $api, ['pid' => $pid]);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除活动奖励', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '删除成功',
        ];
    }

    protected function checkIfRewardInUse($pid)
    {
        $activitiesApi = config('custom.game_api_activities_list');
        $activities = GameApiService::request('GET', $activitiesApi);

        $inUseRewardIds = collect();
        array_walk($activities, function ($activity) use (&$inUseRewardIds) {
            $inUseRewardIds = $inUseRewardIds->merge(explode(',', $activity['reward']));
        });

        //有可能key为0，所以要对比false(找不到使用的pid那么说明此pid未使用)
        if ($inUseRewardIds->search($pid) !== false) {
            throw new CustomException('此奖励正被活动使用中，请先编辑活动再尝试删除此奖励');
        }
        return true;
    }

    public function addReward(AdminRequest $request)
    {
        $formData = $this->validateAddRewardForm($request);
        $api = config('custom.game_api_activities_reward_add');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '添加活动奖励', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '添加成功',
        ];
    }

    protected function validateAddRewardForm($request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:45',
            'img' => 'required|string|max:45',
            'show_text' => 'required|string|max:45',
            'total_inventory' => 'required|integer',
            'probability' => 'required|integer',
            'single_limit' => 'required|integer',
            'goods_type' => 'required|integer',
            'goods_count' => 'required|numeric',
            'whitelist' => 'nullable|string',
            'begin_time' => 'required|required_with_all:end_time|date_format:"Y-m-d H:i:s"',
            'end_time' => 'required|required_with_all:begin_time|date_format:"Y-m-d H:i:s"',
        ]);

        $data = $request->only([
            'name', 'img', 'show_text', 'total_inventory', 'probability',
            'single_limit', 'goods_type', 'goods_count', 'whitelist',
            'begin_time', 'end_time'
        ]);
        
        if (!$data['whitelist']) {  //不然调用api接口报错
            unset($data['whitelist']);
        }
        //转换为时间戳形式
        $data['begin_time'] = Carbon::parse($data['begin_time'])->timestamp;
        $data['end_time'] = Carbon::parse($data['end_time'])->timestamp;
        return $data;
    }
}
