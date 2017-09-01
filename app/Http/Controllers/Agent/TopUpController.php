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
    protected $per_page = 15;
    protected $order = ['id', 'desc'];

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
    }

    //给当前代理商的下级代理商充房卡
    public function topUp2Child(Request $request, $receiver, $type, $amount)
    {
        Validator::make($request->route()->parameters,[
            'receiver' => 'required|string|exists:users,account',
            'type' => 'required|integer|exists:item_type,id',
            'amount' => 'required|integer|not_in:0',
        ])->validate();

        $receiverModel = User::with(['inventory' => function ($query) use ($type) {
            $query->where('item_id', $type);
        }])->where('account', $receiver)->firstOrFail();

        if (! $this->isChild($receiverModel)) {
            return [ 'error' => '只能给您的下级代理商充值' ];
        }

        $this->topUp4Child($receiverModel, $type, $amount);

        //TODO 日志记录
        return [
            'message' => '充值成功'
        ];
    }

    protected function isChild($child)
    {
        return session('user')->id === $child->parent_id;
    }

    protected function topUp4Child($receiver, $type, $amount)
    {
        return DB::transaction(function () use ($receiver, $type, $amount){
            //记录充值流水
            TopUpAgent::create([
                'provider_id' => session('user')->id,
                'receiver_id' => $receiver->id,
                'type' => $type,
                'amount' => $amount,
            ]);

            //更新库存
            if (empty($receiver->inventory)) {
                $receiver->inventory()->create([
                    'user_id' => $receiver->id,
                    'item_id' => $type,
                    'stock' => $amount,
                ]);
            } else {
                $totalStock = $amount + $receiver->inventory->stock;
                $receiver->inventory->update([
                    'stock' => $totalStock,
                ]);
            }
        });
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
                'provider_id' => session('user')->id,
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
    public function topUp2ChildHistory(Request $request)
    {
        OperationLogs::add(session('user')->id, $request->path(), $request->method(),
            '查看给子代理商的充值记录', $request->header('User-Agent'), json_encode($request->all()));

        //搜索下级代理商
        if ($request->has('filter')) {
            $receivers = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');
            if (empty($receivers)) {
                return null;
            }
            return  TopUpAgent::with(['provider', 'receiver', 'item'])
                ->whereIn('receiver_id', $receivers)
                ->orderBy($this->order[0], $this->order[1])
                ->paginate($this->per_page);
        }

        return TopUpAgent::with(['provider', 'receiver', 'item'])
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
        //return TopUpAgent::with(['provider', 'receiver', 'item'])->where('provider_id', session('user')->id)->get();
    }

    public function topUp2PlayerHistory(Request $request)
    {
        OperationLogs::add(session('user')->id, $request->path(), $request->method(),
            '查看代理商给玩家充值记录', $request->header('User-Agent'), json_encode($request->all()));

        //搜索provider
        if ($request->has('filter')) {
            /*$accounts = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');
            if (empty($accounts)) {
                return null;
            }*/
            return TopUpPlayer::with(['provider', 'item'])
                ->where('player', 'like', "%{$request->filter}%")
                ->where('provider_id', session('user')->id)     //只能查看自己给玩家的充值
                ->orderBy($this->order[0], $this->order[1])
                ->paginate($this->per_page);
        }

        return TopUpPlayer::with(['provider', 'item'])
            ->where('provider_id', session('user')->id)
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }
}