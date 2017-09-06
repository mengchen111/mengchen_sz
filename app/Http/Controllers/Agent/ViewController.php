<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/31/17
 * Time: 09:56
 */

namespace App\Http\Controllers\Agent;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function home(Request $request)
    {
        return view('agent.home');
    }

    public function playerTopUp()
    {
        return view('agent.player.top-up');
    }

    public function stockApplyRequest()
    {
        return view('agent.stock.apply-request');
    }

    public function subagentList()
    {
        return view('agent.subagent.list');
    }

    public function subagentCreate()
    {
        return view('agent.subagent.create');
    }

    public function topUpChild()
    {
        return view('agent.top-up.child');
    }

    public function topUpPlayer()
    {
        return view('agent.top-up.player');
    }

    public function info()
    {
        return view('agent.info');
    }
}