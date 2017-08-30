<?php

namespace App\Http\Controllers\Agent;

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
    protected $currentAgent = [];

    public function __construct(Request $request)
    {
        //TODO 登录功能完成之后更改
        $this->currentAgent = session('user') ? session('user') : User::find(2);
    }

    //给当前代理商的下级代理商充房卡
    public function topUp2Child(Request $request, $receiver, $amount)
    {
        Validator::make($request->route()->parameters,[
            'receiver' => 'string|exists:users,account',
            'amount' => 'integer|not_in:0',
        ])->validate();

        $receiver = User::where('account', $receiver)->firstOrFail();

        if (! $this->isChild($receiver)) {
            return [
                'error' => '只能给您的下级代理商充值'
            ];
        }

        DB::transaction(function () use ($receiver, $amount){
            TopUpAgent::create([
                'provider_id' => $this->currentAgent->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
            ]);

            $totalCards = $amount + $receiver->cards;
            $receiver->update([
                'cards' => $totalCards,
            ]);
        });

        //TODO 日志记录
        return [
            'message' => '充值成功',
        ];
    }

    protected function isChild($child)
    {
        return $this->currentAgent->id === $child->parent_id;
    }

    public function topUp2Player(Request $request, $receiver, $amount)
    {
        Validator::make($request->route()->parameters,[
            'receiver' => 'string',
            'amount' => 'integer|not_in:0',
        ])->validate();

        //查找玩家
        //$receiver = User::where('account', $receiver)->firstOrFail();

        //判断是否为本代理商自己的玩家
        /*if (! $this->isMyPlayer($receiver)) {
            return [
                'error' => '只能给您的下级代理商充值'
            ];
        }*/

        //TODO 完善玩家充值流程
        DB::transaction(function () use ($receiver, $amount){
            TopUpAgent::create([
                'provider_id' => $this->currentAgent->id,
                'player' => $receiver->id,
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

    //给下级代理商的充卡记录
    public function topUp2ChildHistory()
    {
        //TODO 日志记录
        return TopUpAgent::with(['provider', 'receiver'])->where('provider_id', $this->currentAgent->id)->get();
    }

    public function topUp2PlayerHistory()
    {
        //TODO 日志记录
        return TopUpPlayer::with(['provider'])->where('provider_id', $this->currentAgent->id)->get();
    }
}