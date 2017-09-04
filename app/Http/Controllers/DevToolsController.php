<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

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
}