<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use App\Models\OperationLogs;
use App\Services\Paginator;

class ActivitiesGoodsController extends Controller
{
    public function getGoodsList(AdminRequest $request)
    {
        $goodsApi = config('custom.game_api_activities_activities-goods-type');
        $goods = GameApiService::request('GET', $goodsApi);
        krsort($goods);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取活动道具列表', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($goods, $this->per_page, $this->page);
    }

    public function getGoodsTypeMap(AdminRequest $request)
    {
        $goodsApi = config('custom.game_api_activities_activities-goods-type');
        $goodsTypes = GameApiService::request('GET', $goodsApi);
        $goodsTypeMap = [];
        array_walk($goodsTypes, function ($goodsType) use (&$goodsTypeMap) {
            $goodsTypeMap[$goodsType['goods_id']] = $goodsType['goods_name'];
        });

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取活动道具列表', $request->header('User-Agent'), json_encode($request->all()));

        return $goodsTypeMap;
    }
}
