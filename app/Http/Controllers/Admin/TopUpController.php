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
    //给总代理商充卡
    public function topUp2TopAgent(Request $request, $receiver, $amount)
    {
        Validator::make($request->route()->parameters,[
            'receiver' => 'string|exists:users,account',
            'amount' => 'integer|not_in:0',
        ])->validate();

        //TODO 完成权限控制之后需要更新session此值
        $provider = session('user') ? session('user')->id : 1;

        $receiver = User::where('account', $receiver)->firstOrFail();

        if (! $this->isTopAgent($receiver)) {
            return [ 'error' => '只能给总代理商充值' ];
        }

        $amount += $receiver->cards;

        DB::transaction(function () use ($provider, $receiver, $amount){
            TopUpAdmin::create([
                'provider_id' => $provider,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
            ]);

            $receiver->update([
                'cards' => $amount,
            ]);
        });

        //TODO 日志记录
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
        //TODO 日志记录
        return TopUpAdmin::with(['provider', 'receiver'])->get();
    }

    //上级代理商给下级的充卡记录
    public function Agent2AgentHistory()
    {
        //TODO 日志记录
        return TopUpAgent::with(['provider', 'receiver'])->get();
    }

    //代理商给玩家的充卡记录
    public function Agent2PlayerHistory()
    {
        //TODO 日志记录
        return TopUpPlayer::with('provider')->get();
    }

}