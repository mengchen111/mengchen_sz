<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use App\Models\OperationLogs;
use App\Services\Paginator;
use App\Exceptions\CustomException;

class ActivitiesGoodsController extends Controller
{
    public function getGoodsList(AdminRequest $request)
    {
        $goodsApi = config('custom.game_api_activities_goods-type_list');
        $goods = GameApiService::request('GET', $goodsApi);
        krsort($goods);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取活动道具列表', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($goods, $this->per_page, $this->page);
    }

    public function getGoodsTypeMap(AdminRequest $request)
    {
        $goodsApi = config('custom.game_api_activities_goods-type_list');
        $goodsTypes = GameApiService::request('GET', $goodsApi);
        $goodsTypeMap = [];
        array_walk($goodsTypes, function ($goodsType) use (&$goodsTypeMap) {
            $goodsTypeMap[$goodsType['goods_id']] = $goodsType['goods_name'];
        });

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取活动道具列表', $request->header('User-Agent'), json_encode($request->all()));

        return $goodsTypeMap;
    }

    public function addGoodsType(AdminRequest $request)
    {
        $this->validate($request, [
            'goods_name' => 'required|string',
        ]);
        $formData = [
            'goods_name' => $request->input('goods_name'),
        ];
        $api = config('custom.game_api_activities_goods-type_add');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '添加活动奖品道具', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '添加成功',
        ];
    }

    public function editGoodsType(AdminRequest $request)
    {
        $this->validate($request, [
            'goods_id' => 'required|integer',
            'goods_name' => 'required|string',
        ]);
        $formData = $request->only(['goods_id', 'goods_name']);

        $api = config('custom.game_api_activities_goods-type_modify');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '编辑活动奖品道具', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '编辑成功',
        ];
    }

    public function deleteGoodsType(AdminRequest $request, $goodsId)
    {
        //查看此奖品道具是否有被activity_reward使用
        $this->checkIfGoodsTypeInUse($goodsId);

        $api = config('custom.game_api_activities_goods-type_delete');
        GameApiService::request('POST', $api, ['goods_id' => $goodsId]);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除活动奖品道具', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '删除成功',
        ];
    }

    protected function checkIfGoodsTypeInUse($goodsId)
    {
        $activityRewardApi = config('custom.game_api_activities_reward_list');
        $activityRewards = GameApiService::request('GET', $activityRewardApi);

        $inUseGoodsIds = collect($activityRewards)->pluck('goods_type');

        //有可能key为0，所以要对比false(找不到使用的pid那么说明此pid未使用)
        if ($inUseGoodsIds->search($goodsId) !== false) {
            throw new CustomException('此奖品道具正被activity_reward使用中，请先编辑奖励列表再尝试删除此道具');
        }
        return true;
    }
}
