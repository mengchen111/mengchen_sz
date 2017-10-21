<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Mockery\Exception;
use Carbon\Carbon;

class DevToolsController extends Controller
{
    public function listSession(Request $request)
    {
        return $request->session()->all();
    }

    public function hashedPass($pass)
    {
        //特殊字符会被过滤掉
        return bcrypt($pass);
    }

    public function base64Decode(Request $request)
    {
        try {
            return base64_decode($request->code);
        } catch (Exception $e) {
            throw new CustomException('base64 转码错误');
        }
    }
}