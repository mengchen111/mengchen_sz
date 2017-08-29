<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TopUpAdmin;
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
        $providerId = session('user') ? session('user')->id : 1;
        $receiver = User::where('account', $receiver)->firstOrFail();

        $amount += $receiver->cards;

        DB::transaction(function () use ($providerId, $receiver, $amount){
            TopUpAdmin::create([
                'provider_id' => $providerId,
                'receiver_id' => $receiver->id,
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

    //查看管理员给总代的充卡历史
    public function topUp2AgentHistory()
    {

    }
}