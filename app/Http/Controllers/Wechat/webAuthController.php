<?php

namespace App\Http\Controllers\Wechat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;

class webAuthController extends Controller
{
    //微信网页授权回调，返送code(用于申请access_token拿用户信息)和state参数过来
    //使用路由后此回调方法不需要了
    public function callback(Request $request)
    {
        OperationLogs::add(0, $request->path(), $request->method(),
            '微信网页授权回调', $request->header('User-Agent'), json_encode($request->all()));

//        $wechat = app('wechat');
//        $oauth = $wechat->oauth;
//        $user = $oauth->user();
    }
}
