<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TopUpAdmin;
use App\Models\TopUpAgent;
use App\Models\TopUpPlayer;
use App\Models\Game\Player;
use App\Models\ItemType;
use Illuminate\Support\Facades\Validator;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\DB;

class TopUpController extends Controller
{
    protected $per_page = 15;
    protected $order = ['id', 'desc'];
    protected $cardItemId = 1030005;    //房卡在游戏库中的id号

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
    }

    //给代理商充值（自己的下级）
    public function topUp2Agent(Request $request, $receiver, $type, $amount)
    {
        Validator::make($request->route()->parameters,[
            'receiver' => 'required|string|exists:users,account',
            'type' => 'required|integer|exists:item_type,id',
            'amount' => 'required|integer|not_in:0',
        ])->validate();

        $provider = User::with(['inventory' => function ($query) use ($type) {
            $query->where('item_id', $type);
        }])->find(session('user')->id);
        $receiverModel = User::with(['inventory' => function ($query) use ($type) {
            $query->where('item_id', $type);
        }])->where('account', $receiver)->firstOrFail();

        if (! $this->isChild($receiverModel)) {
            return [ 'error' => '只能给自己的下级代理商充值' ];
        }

        if (! $this->checkStock($provider, $amount)) {
            return [ 'error' => '库存不足，无法充值'];
        }

        $this->topUp4Child($provider, $receiverModel, $type, $amount);

        OperationLogs::add(session('user')->id, $request->path(), $request->method(),
            '管理员给代理商充值', $request->header('User-Agent'), json_encode($request->route()->parameters));
        return [
            'message' => '充值成功',
        ];
    }

    protected function isChild($receiver)
    {
        return session('user')->id === $receiver->parent_id;
    }

    protected function checkStock(User $provider, $amount)
    {
        return (! empty($provider->inventory)) and $provider->inventory->stock >= $amount;
    }

    protected function topUp4Child($provider, $receiver, $type, $amount)
    {
        return DB::transaction(function () use ($provider, $receiver, $type, $amount){
            //记录充值流水
            TopUpAdmin::create([
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

            //减管理员的库存
            $leftStock = $provider->inventory->stock - $amount;
            $provider->inventory->update([
                'stock' => $leftStock,
            ]);
        });
    }

    //管理员给总代的充值记录
    public function topUp2TopAgentHistory(Request $request)
    {
        OperationLogs::add(session('user')->id, $request->path(), $request->method(),
            '管理员查看其充值记录', $request->header('User-Agent'), json_encode($request->all()));

        //搜索代理商
        if ($request->has('filter')) {
            $receivers = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');
            if (empty($receivers)) {
                return null;
            }
            return  TopUpAdmin::with(['provider', 'receiver', 'item'])
                ->whereIn('receiver_id', $receivers)
                ->orderBy($this->order[0], $this->order[1])
                ->paginate($this->per_page);
        }

        return TopUpAdmin::with(['provider', 'receiver', 'item'])
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }

    //上级代理商给下级的充值记录
    public function Agent2AgentHistory(Request $request)
    {
        OperationLogs::add(session('user')->id, $request->path(), $request->method(),
            '管理员查看代理商充值记录', $request->header('User-Agent'), json_encode($request->all()));

        //搜索代理商，查找字符串包括发放者和接收者
        if ($request->has('filter')) {
            $accounts = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');
            if (empty($accounts)) {
                return null;
            }
            return TopUpAgent::with(['provider', 'receiver', 'item'])
                ->whereIn('provider_id', $accounts)
                ->whereIn('receiver_id', $accounts, 'or')
                ->orderBy($this->order[0], $this->order[1])
                ->paginate($this->per_page);
        }

        return TopUpAgent::with(['provider', 'receiver', 'item'])
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }

    //代理商给玩家的充值记录
    public function Agent2PlayerHistory(Request $request)
    {
        OperationLogs::add(session('user')->id, $request->path(), $request->method(),
            '管理员查看代理商给玩家充值记录', $request->header('User-Agent'), json_encode($request->all()));

        //搜索provider
        if ($request->has('filter')) {
            $accounts = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');
            if (empty($accounts)) {
                return null;
            }
            return TopUpPlayer::with(['provider', 'item'])
                ->whereIn('provider_id', $accounts)
                ->orderBy($this->order[0], $this->order[1])
                ->paginate($this->per_page);
        }

        return TopUpPlayer::with(['provider', 'item'])
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }

    /**
     * @param Request $request
     * @param $player   玩家id
     * @param $type     道具类型
     * @param $amount   数量
     */
    public function topUp2Player(Request $request, $player, $type, $amount)
    {
        Validator::make($request->route()->parameters,[
            'player' => 'required|string|exists:mysql-game.role,rid',
            'type' => 'required|integer|exists:item_type,id',
            'amount' => 'required|integer|not_in:0',
        ])->validate();

        $provider = User::with(['inventory' => function ($query) use ($type) {
            $query->where('item_id', $type);
        }])->find(session('user')->id);
        $playerModel = Player::with(['card'])->where('rid', $player)->firstOrFail();
        $itemType = ItemType::find($type);

        if (! $this->checkStock($provider, $amount)) {
            return [ 'error' => '库存不足，无法充值'];
        }

        switch ($itemType->name) {
            case '房卡':
                $this->topUpCard4Player($provider, $playerModel, $type, $amount);
                break;
            case '金币':
                $this->topUpGold4Player($provider, $playerModel, $type, $amount);
                break;
            default:
                return ['error' => '只能充值房卡和金币'];
        }

        OperationLogs::add(session('user')->id, $request->path(), $request->method(),
            '管理员给玩家充值', $request->header('User-Agent'), json_encode($request->route()->parameters));
        return [
            'message' => '充值成功',
        ];
    }

    /**
     * @param $provider 管理员模型
     * @param $player   玩家模型
     * @param $type     道具id
     * @param $amount   道具数量
     */
    protected function topUpGold4Player($provider, $player, $type, $amount)
    {
        return DB::transaction(function () use ($provider, $player, $type, $amount){
            //记录充值流水
            TopUpPlayer::create([
                'provider_id' => session('user')->id,
                'player' => $player->rid,
                'type' => $type,
                'amount' => $amount,
            ]);

            //更新库存
            $totalStock = $amount + $player->gold;
            $player->update([
                'gold' => $totalStock,
            ]);

            //减管理员的库存
            $leftStock = $provider->inventory->stock - $amount;
            $provider->inventory->update([
                'stock' => $leftStock,
            ]);
        });
    }

    protected function topUpCard4Player($provider, $player, $type, $amount)
    {
        return DB::transaction(function () use ($provider, $player, $type, $amount){
            //记录充值流水
            TopUpPlayer::create([
                'provider_id' => session('user')->id,
                'player' => $player->rid,
                'type' => $type,
                'amount' => $amount,
            ]);

            //更新库存
            if (empty($player->card)) {
                $player->card()->create([
                    'rid' => $player->rid,
                    'item_id' => $this->cardItemId,
                    'expire' => 0,
                    'count' => $amount,
                    'sort' => 1,
                    'state' => 1,
                ]);
            } else {
                $totalStock = $amount + $player->card->count;
                $player->card->update([
                    'count' => $totalStock,
                ]);
            }

            //减管理员的库存
            $leftStock = $provider->inventory->stock - $amount;
            $provider->inventory->update([
                'stock' => $leftStock,
            ]);
        });
    }
}