<?php

namespace App\Http\Controllers\Admin\Game;

use App\Http\Requests\AdminRequest;
use Illuminate\Http\Request;
use App\Services\Game\GameApiService;
use App\Models\OperationLogs;
use App\Services\Paginator;
use App\Http\Controllers\Controller;

class ActivitiesUserGoodsController extends Controller
{
    public function getUserGoodsList(AdminRequest $request)
    {
        $userGoodsApi = config('custom.game_api_activities_user-goods_list');
        $userGoodsList = GameApiService::request('GET', $userGoodsApi);
        krsort($userGoodsList);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '获取玩家物品列表', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($userGoodsList, $this->per_page, $this->page);
    }

    public function editUserGoods(AdminRequest $request)
    {
        $formData = $this->validateEditUserGoodsForm($request);

        $api = config('custom.game_api_activities_user-goods_modify');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '编辑玩家物品', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '编辑成功',
        ];
    }

    protected function validateEditUserGoodsForm($request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer',
            'goods_id' => 'required|integer',
            'goods_cnt' => 'required|integer',
        ]);

        return $request->only([
            'user_id', 'goods_id', 'goods_cnt',
        ]);
    }

    public function deleteUserGoods(AdminRequest $request)
    {
        $formData = $request->only(['user_id', 'goods_id']);
        $api = config('custom.game_api_activities_user-goods_delete');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除玩家物品', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '删除成功',
        ];
    }

    public function addUserGoods(AdminRequest $request)
    {
        $formData = $this->validateAddUserGoodsForm($request);

        $api = config('custom.game_api_activities_user-goods_add');
        GameApiService::request('POST', $api, $formData);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '添加玩家物品', $request->header('User-Agent'), json_encode($request->all()));

        return [
            'message' => '添加成功',
        ];
    }

    protected function validateAddUserGoodsForm($request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer',
            'goods_id' => 'required|integer',
            'goods_cnt' => 'required|integer',
        ]);

        return $request->only([
            'user_id', 'goods_id', 'goods_cnt',
        ]);
    }
}
