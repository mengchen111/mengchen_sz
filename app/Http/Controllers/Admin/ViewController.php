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

    public function statementSummary(Request $request)
    {
        return view('admin.statement.summary');
    }

    public function statementOnlinePlayers(Request $request)
    {
        return view('admin.statement.online-players');
    }

    public function statementRoom(Request $request)
    {
        return view('admin.statement.room');
    }

    public function gmRecord(Request $request)
    {
        return view('admin.gm.record');
    }

    public function gmRoom(Request $request)
    {
        return view('admin.gm.room');
    }

    public function activitiesActivitiesList(Request $request)
    {
        return view('admin.activities.activities-list');
    }

    public function activitiesRewardsList(Request $request)
    {
        return view('admin.activities.rewards-list');
    }

    public function activitiesGoodsList(Request $request)
    {
        return view('admin.activities.goods-list');
    }

    public function activitiesTasksList(Request $request)
    {
        return view('admin.activities.tasks-list');
    }

    public function activitiesUserGoods(Request $request)
    {
        return view('admin.activities.user-goods');
    }

    public function activitiesPlayerTask(Request $request)
    {
        return view('admin.activities.player-task');
    }

    public function activitiesStatement(Request $request)
    {
        return view('admin.activities.statement');
    }

    public function activitiesRedPacketLog(Request $request){
        return view('admin.activities.red-packet-log');
    }

    public function communityList(Request $request)
    {
        return view('admin.community.list');
    }

    public function playerList(Request $request)
    {
        return view('admin.player.list');
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

    public function agentCreate(Request $request)
    {
        return view('admin.agent.create');
    }

    public function agentList(Request $request)
    {
        return view('admin.agent.list');
    }

    public function agentBills(Request $request)
    {
        return view('admin.agent.bills');
    }

    public function agentValidCard(Request $request)
    {
        return view('admin.agent.valid-card');
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

    public function permissionMember(Request $request)
    {
        return view('admin.permission.member');
    }

    public function permissionGroup(Request $request)
    {
        return view('admin.permission.group');
    }

    public function systemLog(Request $request)
    {
        return view('admin.system.log');
    }
}