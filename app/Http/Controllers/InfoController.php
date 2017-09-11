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
     * 获取登录用户信息
     *
     * @return \Illuminate\Http\Response
     */
    public function info(Request $request)
    {
        return User::with(['group', 'parent', 'inventorys.item'])->find(Auth::id());
    }
}
