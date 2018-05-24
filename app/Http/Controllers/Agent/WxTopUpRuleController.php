<?php

namespace App\Http\Controllers\Agent;

use App\Models\WxTopUpRule;
use App\Http\Controllers\Controller;

class WxTopUpRuleController extends Controller
{
    /**
     *
     * @SWG\Get(
     *     path="/agent/api/wx-top-up-rules",
     *     description="获取充值套餐",
     *     operationId="agent.wx.top-up-rules.get",
     *     tags={"wx-top-up"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="获取充值套餐成功",
     *         @SWG\Property(
     *             type="array",
     *             @SWG\Items(
     *                 type="object",
     *                 allOf={
     *                     @SWG\Schema(ref="#/definitions/WxTopUpRule"),
     *                 },
     *             ),
     *         ),
     *     ),
     * )
     */
    public function index()
    {
        return WxTopUpRule::select('id', 'price', 'remark')->get()->sortBy('price');
    }
}
