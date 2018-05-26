<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/30/17
 * Time: 16:03
 */

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InfoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * 获取代理商个人信息
     *
     * @SWG\Get(
     *     path="/api/info",
     *     description="获取用户信息",
     *     operationId="user.info",
     *     tags={"user"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回用户信息",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/User"),
     *             },
     *             @SWG\Property(
     *                 property="group",
     *                 description="组信息",
     *                 type="object",
     *                 allOf={
     *                     @SWG\Schema(ref="#/definitions/Group"),
     *                 },
     *             ),
     *             @SWG\Property(
     *                 property="parent",
     *                 description="上级代理商",
     *                 type="object",
     *                 allOf={
     *                     @SWG\Schema(ref="#/definitions/User"),
     *                 },
     *             ),
     *             @SWG\Property(
     *                 property="inventorys",
     *                 description="道具库存",
     *                 type="array",
     *                 @SWG\Items(
     *                     type="object",
     *                     allOf={
     *                         @SWG\Schema(ref="#/definitions/Inventory"),
     *                     },
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function info(Request $request)
    {
        return User::with(['group', 'parent', 'inventorys.item'])->find(Auth::id());
    }

    public function getContentHeaderH1(Request $request)
    {
        return '';  //面包屑导航左边的标题文字
    }
}