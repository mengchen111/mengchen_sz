<?php

namespace App\Http\Controllers\Agent;

use App\Models\WxTopUpRule;
use App\Http\Controllers\Controller;

class WxTopUpRuleController extends Controller
{
    public function index()
    {
        return WxTopUpRule::select('id', 'price', 'remark')->get()->sortBy('price');
    }
}
