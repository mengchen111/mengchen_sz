<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Services\Game\GameApiService;
use App\Services\Game\PlayerService;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use App\Models\User;
use App\Models\TopUpAdmin;
use App\Models\TopUpAgent;
use App\Models\TopUpPlayer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TopUpController extends Controller
{
    protected $per_page = 15;
    protected $order = ['id', 'desc'];
    protected $adminUid = 1;
    protected $itemTypeMap = [      //后台道具的key与游戏后端道具的key的映射关系
        1 => 'ycoins',
        2 => 'ypoints',
    ];

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
    }

    //给代理商充值（自己的下级）
    public function topUp2Agent(AdminRequest $request, $receiver, $type, $amount)
    {
        Validator::make($request->route()->parameters,[
            'receiver' => 'required|string|exists:users,account',
            'type' => 'required|integer|exists:item_type,id',
            'amount' => 'required|integer|not_in:0',
        ])->validate();

        $provider = User::with(['inventory' => function ($query) use ($type) {
            $query->where('item_id', $type);
        }])->find($request->user()->id);
        $receiverModel = User::with([
            'inventory' => function ($query) use ($type) {
                $query->where('item_id', $type);    //在道具类型上做约束
            },
            'parent.inventory' => function ($query) use ($type) {
                $query->where('item_id', $type);
            },
        ])->where('account', $receiver)->firstOrFail();

        //允许管理员给非所有代理商充值房卡
//        if (! $receiverModel->isChild(Auth::id())) {
//            throw new CustomException('只能给您的下级代理商充值');
//        }

        if (! $this->checkStock($provider, $amount)) {
            throw new CustomException('库存不足，无法充值');
        }

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '管理员给代理商充值[减库存]', $request->header('User-Agent'), json_encode($request->route()->parameters));

        //减库存
        if (preg_match('/-/', $amount)) {
            //检查代理商是否存在足够的库存可以被减少
            if (! $this->checkChildHasEnoughStock($receiverModel, $amount)) {
                return [
                    'error' => '此代理商没有足够的库存被减少',
                ];
            }
            $this->cutStock4Child($request, $receiverModel, $type, $amount);
            return [
                'message' => '减库存成功',
            ];
        }

        //加库存
        $this->topUp4Child($request, $provider, $receiverModel, $type, $amount);
        return [
            'message' => '充值成功',
        ];
    }

    protected function checkStock(User $provider, $amount)
    {
        return (! empty($provider->inventory)) and $provider->inventory->stock >= $amount;
    }

    protected function checkChildHasEnoughStock($receiver, $amount)
    {
        $inventory = $receiver->inventory;
        if (empty($inventory) or $inventory->stock < abs($amount)) {
            return false;
        }
        return true;
    }

    //减库存，同时给receiver的上级增加库存
    protected function cutStock4Child($request, $receiver, $type, $amount)
    {
        return DB::transaction(function () use ($request, $receiver, $type, $amount) {
            //更新代理商的库存（减少）
            $this->updateReceiverStock($receiver, $amount);

            //增加receiver的上级代理商的库存
            if (empty($receiver->parent->inventory)) {
                $receiver->parent->inventory()->create([
                    'user_id' => $receiver->parent->id,
                    'item_id' => $type,
                    'stock' => abs($amount),
                ]);
            } else {
                $receiver->parent->inventory->stock += abs($amount);
                $receiver->parent->inventory->save();
            }

            //记录充值流水，如果parent是agent那就记录在agent表中，否则统计时会出现异常
            if ($receiver->parent->is_admin) {
                TopUpAdmin::create([
                    'provider_id' => $receiver->parent->id,
                    'receiver_id' => $receiver->id,
                    'type' => $type,
                    'amount' => $amount,
                ]);
            } else {
                TopUpAgent::create([
                    'provider_id' => $receiver->parent->id,
                    'receiver_id' => $receiver->id,
                    'type' => $type,
                    'amount' => $amount,
                ]);
            }
        });
    }

    protected function updateReceiverStock($receiver, $amount)
    {
        $receiver->inventory->stock += $amount;
        return $receiver->inventory->save();
    }

    protected function topUp4Child($request, $provider, $receiver, $type, $amount)
    {
        return DB::transaction(function () use ($request, $provider, $receiver, $type, $amount){
            //更新库存
            if (empty($receiver->inventory)) {
                $receiver->inventory()->create([
                    'user_id' => $receiver->id,
                    'item_id' => $type,
                    'stock' => $amount,
                ]);
            } else {
                $this->updateReceiverStock($receiver, $amount);
            }

            //减管理员的库存
            $provider->inventory->stock -= $amount;
            $provider->inventory->save();

            //记录充值流水
            TopUpAdmin::create([
                'provider_id' => $request->user()->id,
                'receiver_id' => $receiver->id,
                'type' => $type,
                'amount' => $amount,
            ]);
        });
    }

    //管理员给代理商的充值记录
    public function admin2AgentHistory(AdminRequest $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看管理员充值记录', $request->header('User-Agent'), json_encode($request->all()));

        //搜索代理商
        if ($request->has('filter')) {
            $receivers = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');

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
    public function agent2AgentHistory(AdminRequest $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看代理商充值记录', $request->header('User-Agent'), json_encode($request->all()));

        //搜索代理商，查找字符串包括发放者和接收者
        if ($request->has('filter')) {
            $accounts = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');

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
    public function agent2PlayerHistory(AdminRequest $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看玩家充值记录', $request->header('User-Agent'), json_encode($request->all()));

        //搜索provider或玩家
        if ($request->has('filter')) {
            $accounts = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');

            return TopUpPlayer::with(['provider', 'item'])
                ->where('player', 'like', "%{$request->filter}%")
                ->whereIn('provider_id', $accounts, 'or')
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
    public function topUp2Player(AdminRequest $request, $player, $type, $amount)
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

        //清空玩家列表缓存
        Cache::pull(config('custom.game_server_cache_players'));

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '管理员给玩家充值', $request->header('User-Agent'), json_encode($request->route()->parameters));

        //减库存
        if (preg_match('/-/', $amount)) {
            //检查玩家是否拥有足够的库存可以被减少
            if (! $this->checkPlayerHasEnoughStock($player, $type, $amount)) {
                return [
                    'error' => '此玩家没有足够的库存被减少',
                ];
            }
            $this->cutStock4Player($request, $player, $type, $amount);
            return [
                'message' => '减库存成功',
            ];
        }

        //加库存
        $this->topUp4Player($request, $provider, $player, $type, $amount);

        return [
            'message' => '充值成功',
        ];
    }

    protected function checkPlayerHasEnoughStock($playerId, $type, $amount)
    {
        $player = PlayerService::searchPlayers($playerId)[0];
        $stock = $player[$this->itemTypeMap[$type]];
        return $stock >= abs($amount);
    }

    protected function cutStock4Player($request, $player, $type, $amount)
    {
        return DB::transaction(function () use ($request, $player, $type, $amount){
            //加管理员的库存（减掉的库存，返回到admin的库存下面）
            $admin = User::with(['inventory' => function ($query) use ($type) {
                $query->where('item_id', $type);
            }])->find($this->adminUid);
            $admin->inventory->stock += abs($amount);
            $admin->inventory->save();

            //记录充值流水
            TopUpPlayer::create([
                'provider_id' => $this->adminUid,
                'player' => $player,
                'type' => $type,
                'amount' => $amount,
            ]);

            //调用接口减库存（与充值为同一接口）
            $this->sendTopUpRequest([
                'uid' => $player,
                'item_type' => $type,
                'amount' => $amount,
            ]);
        });
    }

    protected function topUp4Player($request, $provider, $player, $type, $amount)
    {
        return DB::transaction(function () use ($request, $provider, $player, $type, $amount){
            //减管理员的库存
            $provider->inventory->stock -= $amount;
            $provider->inventory->save();

            //记录充值流水
            TopUpPlayer::create([
                'provider_id' => $request->user()->id,
                'player' => $player,
                'type' => $type,
                'amount' => $amount,
            ]);

            //调用接口充值（放最后，失败回滚才会正常）
            $this->sendTopUpRequest([
                'uid' => $player,
                'item_type' => $type,
                'amount' => $amount,
            ]);
        });
    }

    protected function sendTopUpRequest($params)
    {
        $playerTopUpApi = config('custom.game_api_top-up');
        return GameApiService::request('POST', $playerTopUpApi, $params);
    }
}