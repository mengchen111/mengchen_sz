<?php

namespace App\Http\Controllers\Admin\Game;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
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

        $this->activitiesRewardApi = config('custom.game_api_activities_activities-reward');
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
        $this->validateEditRewardForm($request);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '编辑活动奖品', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'data' => $request->all(),
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
        ]);
    }

    public function deleteReward(AdminRequest $request, $pid)
    {
        //查看此奖励是否有被活动使用
        $this->checkIfRewardInUse($pid);

        return [
            'data' => $pid,
        ];
    }

    protected function checkIfRewardInUse($pid)
    {
        $activitiesApi = config('custom.game_api_activities_activities-list');
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
        $this->validateAddRewardForm($request);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '添加活动奖品', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'data' => $request->all(),
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
            'expend' => 'required|integer',
        ]);
    }
}
