<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 8/31/17
 * Time: 09:56
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest as Request;

class ViewController extends Controller
{
    public function home(Request $request)
    {
        return view('admin.home');
    }

    public function StatementCard(Request $request)
    {
        return view('admin.statement.card');
    }

    public function playerList(Request $request)
    {
        return view('admin.player.list');
    }

    public function notificationMarquee(Request $request)
    {
        return view('admin.notification.marquee');
    }

    public function notificationLogin(Request $request)
    {
        return view('admin.notification.login');
    }

    public function stockApplyRequest(Request $request)
    {
        return view('admin.stock.apply-request');
    }

    public function stockApplyList(Request $request)
    {
        return view('admin.stock.apply-list');
    }

    public function stockApplyHistory(Request $request)
    {
        return view('admin.stock.apply-history');
    }

    public function agentList(Request $request)
    {
        return view('admin.agent.list');
    }

    public function agentCreate(Request $request)
    {
        return view('admin.agent.create');
    }

    public function topUpAdmin(Request $request)
    {
        return view('admin.top-up.admin');
    }

    public function topUpAgent(Request $request)
    {
        return view('admin.top-up.agent');
    }

    public function topUpPlayer(Request $request)
    {
        return view('admin.top-up.player');
    }

    public function systemLog(Request $request)
    {
        return view('admin.system.log');
    }
}