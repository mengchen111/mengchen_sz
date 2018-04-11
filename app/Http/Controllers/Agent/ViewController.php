<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/31/17
 * Time: 09:56
 */

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRequest as Request;

class ViewController extends Controller
{
    public function home(Request $request)
    {
        return view('agent.home');
    }

    public function playerTopUp(Request $request)
    {
        return view('agent.player.top-up');
    }

    public function stockApplyRequest(Request $request)
    {
        return view('agent.stock.apply-request');
    }

    public function stockApplyHistory(Request $request)
    {
        return view('agent.stock.apply-history');
    }

    public function subagentList(Request $request)
    {
        return view('agent.subagent.list');
    }

    public function subagentCreate(Request $request)
    {
        return view('agent.subagent.create');
    }

    public function communityList(Request $request)
    {
        return view('agent.community.list');
    }

    public function communityManage(Request $request)
    {
        return view('agent.community.manage');
    }

    public function communityTopUp(Request $request)
    {
        return view('agent.community.community-top-up');
    }

    public function topUpChild(Request $request)
    {
        return view('agent.top-up.child');
    }

    public function topUpPlayer(Request $request)
    {
        return view('agent.top-up.player');
    }

    public function topUpCommunity(Request $request)
    {
        return view('agent.top-up.community');
    }

    public function info(Request $request)
    {
        return view('agent.info');
    }

    public function rebates(Request $request)
    {
        return view('agent.pay.rebates');
    }
    public function withdrawals(Request $request)
    {
        return view('agent.pay.withdrawals');
    }

    public function wxOrder(Request $request)
    {
        return view('agent.pay.wx-order');
    }
}