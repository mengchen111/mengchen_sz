<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/30/17
 * Time: 16:03
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class InfoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 获取登录用户信息
     *
     * @return \Illuminate\Http\Response
     */
    public function info(Request $request)
    {
        return $request->session()->get('user');
    }
}
