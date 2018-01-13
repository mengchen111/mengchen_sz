<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function webAuth(Request $request)
    {
        return view('wechat.web-auth');
    }
}