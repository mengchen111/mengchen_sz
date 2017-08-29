<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TopUpAdmin;
use App\Models\TopUpAgent;
use App\Models\TopUpPlayer;
use Illuminate\Support\Facades\Validator;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\DB;

class TopUpController extends Controller
{
    //给代理商充卡
    public function topUp2Agent(Request $request, $receiver, $amount)
    {
        Validator::make($request->route()->parameters,[
            'receiver' => 'string|exists:users,account',
            'amount' => 'integer|not_in:0',
        ])->validate();

        //TODO 完成权限控制之后需要更新session此值
        $provider = session('user') ? session('user')->account : 'admin';

        $receiver = User::where('account', $receiver)->firstOrFail();

        if (! $this->isTopAgent($receiver)) {
            return [ 'error' => '只能给总代充值' ];
        }

        $amount += $receiver->cards;

        DB::transaction(function () use ($provider, $receiver, $amount){
            TopUpAdmin::create([
                'provider' => $provider,
                'receiver' => $receiver->account,
                'amount' => $amount,
            ]);

            $receiver->update([
                'cards' => $amount,
            ]);
        });

        return [
            'message' => '充值成功',
        ];
    }

    protected function isTopAgent($agent)
    {
        return 2 == $agent->group_id;
    }

    //管理员给总代的充卡记录
    public function topUp2TopAgentHistory()
    {
        return TopUpAdmin::all();
    }

    //上级代理商给下级的充卡记录
    public function Agent2AgentHistory()
    {
        return TopUpAgent::all();
    }

    //代理商给玩家的充卡记录
    public function Agent2PlayerHistory()
    {
        return TopUpPlayer::all();
    }

}