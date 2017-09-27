<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TopUpAgent;
use App\Models\TopUpPlayer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\DB;
use App\Services\GameServer;
use Carbon\Carbon;

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

        $provider = User::with(['inventory' => function ($query) use ($type) {
            $query->where('item_id', $type);
        }])->find($request->user()->id);
        $receiverModel = User::with(['inventory' => function ($query) use ($type) {
            $query->where('item_id', $type);
        }])->where('account', $receiver)->firstOrFail();

        if (! $receiverModel->isChild(Auth::id())) {
            throw new CustomException('只能给您的下级代理商充值');
        }

        if (! $this->checkStock($provider, $amount)) {
            throw new CustomException('库存不足，无法充值');
        }

        $this->topUp4Child($request, $provider, $receiverModel, $type, $amount);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '代理商给子代理商充值', $request->header('User-Agent'), json_encode($request->route()->parameters));

        return [
            'message' => '充值成功'
        ];
    }

    protected function checkStock(User $provider, $amount)
    {
        return (! empty($provider->inventory)) and $provider->inventory->stock >= $amount;
    }

    protected function topUp4Child($request, $provider, $receiver, $type, $amount)
    {
        return DB::transaction(function () use ($request, $provider, $receiver, $type, $amount){
            //记录充值流水
            TopUpAgent::create([
                'provider_id' => $request->user()->id,
                'receiver_id' => $receiver->id,
                'type' => $type,
                'amount' => $amount,
            ]);

            //更新下级代理库存
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

            //更新自己的库存
            $leftStock = $provider->inventory->stock - $amount;
            $provider->inventory->update([
                'stock' => $leftStock,
            ]);
        });
    }

    //给下级代理商的充卡记录
    public function topUp2ChildHistory(Request $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '代理商查看其给子代理充值记录', $request->header('User-Agent'), json_encode($request->all()));

        //搜索下级代理商
        if ($request->has('filter')) {
            $receivers = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');
            if (empty($receivers)) {
                return null;
            }
            return  TopUpAgent::with(['provider', 'receiver', 'item'])
                ->whereIn('receiver_id', $receivers)
                ->where('provider_id', $request->user()->id)
                ->orderBy($this->order[0], $this->order[1])
                ->paginate($this->per_page);
        }

        return TopUpAgent::with(['provider', 'receiver', 'item'])
            ->where('provider_id', $request->user()->id)
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }

    public function topUp2PlayerHistory(Request $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '代理商查看其给玩家充值记录', $request->header('User-Agent'), json_encode($request->all()));

        //搜索provider
        if ($request->has('filter')) {
            return TopUpPlayer::with(['provider', 'item'])
                ->where('player', 'like', "%{$request->filter}%")
                ->where('provider_id', $request->user()->id)     //只能查看自己给玩家的充值
                ->orderBy($this->order[0], $this->order[1])
                ->paginate($this->per_page);
        }

        return TopUpPlayer::with(['provider', 'item'])
            ->where('provider_id', $request->user()->id)
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }

    public function topUp2Player(Request $request, $player, $type, $amount)
    {
        Validator::make($request->route()->parameters,[
            'player' => 'required|integer',
            'type' => 'required|integer|exists:item_type,id',
            'amount' => 'required|integer|not_in:0',
        ])->validate();

        $provider = User::with(['inventory' => function ($query) use ($type) {
            $query->where('item_id', $type);
        }])->find($request->user()->id);

        if (! $this->checkStock($provider, $amount)) {
            throw new CustomException('库存不足，无法充值');
        }

        $this->topUp4Player($request, $provider, $player, $type, $amount);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '代理商给玩家充值', $request->header('User-Agent'), json_encode($request->route()->parameters));

        return [
            'message' => '充值成功',
        ];
    }

    protected function topUp4Player($request, $provider, $player, $type, $amount)
    {
        return DB::transaction(function () use ($request, $provider, $player, $type, $amount){
            //记录充值流水
            TopUpPlayer::create([
                'provider_id' => $request->user()->id,
                'player' => $player,
                'type' => $type,
                'amount' => $amount,
            ]);

            //调用接口充值
            $this->sendTopUpRequest([
                'uid' => $player,
                'ctype' => $type,
                'amount' => $amount,
                'timestamp' => Carbon::now()->timestamp
            ]);

            //减库存
            $leftStock = $provider->inventory->stock - $amount;
            $provider->inventory->update([
                'stock' => $leftStock,
            ]);
        });
    }

    protected function sendTopUpRequest($params)
    {
        $gameServer = new GameServer();

        try {
            return $gameServer->request('POST', 'recharge.php', $params);
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }
}