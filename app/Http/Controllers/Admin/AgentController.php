<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Services\Game\PlayerService;
use App\Services\Game\ValidCardConsumedService;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\OperationLogs;

class AgentController extends Controller
{
    protected $agentGroups = [2, 3, 4];     //agent代理商的组id号

    public function showAll(AdminRequest $request)
    {
        //给per_page设定默认值，比起参数默认值这样子可以兼容uri传参和变量名传参，变量名传递过来的参数优先
        $per_page = $request->per_page ?: 15;
        $order = $request->sort ? explode('|', $request->sort) : ['id', 'desc'];

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看代理商列表', $request->header('User-Agent'));

        //查找用户名或昵称
        $data = User::with(['group', 'parent', 'inventorys.item', 'agentTopUpRecords', 'playerTopUpRecords'])
            ->when($request->has('filter'), function ($query) use ($request) {
                return $query->where('account', 'like', "%{$request->filter}%")
                    ->where('name', 'like', "%{$request->filter}%", 'or');
            })
            ->whereIn('group_id', $this->agentGroups)
            ->orderBy($order[0], $order[1])
            ->paginate($per_page);

        $data = $this->addDataOnAgent($data);   //添加额外的数据
        return $data;
    }

    //输出的代理商列表数据，添加额外的数据
    protected function addDataOnAgent($data)
    {
        foreach ($data->items() as $user) {
            //每个代理商添加累计售卡数
            $itemSoldTotal = [];
            $agentSoldCount = $user->agentTopUpRecords->groupBy('type')->map(function ($v) {
                return $v->sum('amount');
            });
            $playerSoldCount = $user->playerTopUpRecords->groupBy('type')->map(function ($v) {
                return $v->sum('amount');
            });
            foreach ($agentSoldCount as $k => $v) {
                $itemSoldTotal[$k] = $v;
            }
            foreach ($playerSoldCount as $k => $v) {
                if (array_key_exists($k, $itemSoldTotal)) {
                    $itemSoldTotal[$k] += $v;
                } else {
                    $itemSoldTotal[$k] = $v;
                }
            }
            $user['item_sold_total'] = $itemSoldTotal;
            unset($user->agentTopUpRecords);
            unset($user->playerTopUpRecords);

            //添加有效耗卡数
            $user['valid_card_consumed_num'] = ValidCardConsumedService::getAgentValidCardConsumedNum($user->id);
        }
        return $data;
    }

    //创建代理商
    public function create(AdminRequest $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'account' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'email' => 'string|email|max:255',
            'phone' => 'integer|digits:11',
            'group_id' => 'required|integer|not_in:1|exists:groups,id',  //管理员不能创建管理员
        ])->validate();

        $data = $request->intersect(
            'name', 'account', 'password', 'email', 'phone', 'group_id'
        );

        $data['password'] = bcrypt($data['password']);
        $data = array_merge($data, ['parent_id' => $request->user()->id]);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(), '管理员添加代理商',
            $request->header('User-Agent'), json_encode($data));

        return User::create($data);
    }

    public function destroy(AdminRequest $request, User $user)
    {
        if ($user->is_admin) {
            throw new CustomException('不能删除管理员');
        }

        if ($user->hasChild()) {
            throw new CustomException('此代理商下存在下级代理');
        }

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除代理商', $request->header('User-Agent'), $user->toJson());

        $user->delete();

        return [
            'message' => '删除成功'
        ];
    }

    public function update(AdminRequest $request, User $user)
    {
        $input = $request->all();

        //如果提交的用户名和用户本身的用户名相同，就不进行Validate，不然unique验证失败
        if ($request->has('account') and ($request->input('account') == $user->account)) {
            unset($input['account']);
        }

        Validator::make($input, [
            'name' => 'string|max:255',
            'account' => 'string|max:255|unique:users',
            'email' => 'string|email|max:255',
            'phone' => 'integer|digits:11',
            'group_id' => 'integer|not_in:1|exists:groups,id',   //不能将代理商改成管理员
            'parent_account' => 'string',
        ])->validate();

        $data = $request->intersect(
            'name', 'account', 'email', 'phone', 'group_id'
        );

        //更改代理商级别
        if ($request->has('group_id')) {
            if (! $this->ifCanChangeGroup($user, $request->input('group_id'))) {
                throw new CustomException('新的代理商级别不能等于或低于其下级代理商的级别');
            }
            $data = array_merge($data, ['group_id' => $request->input('group_id')]);
        }

        //更新上级
        if ($request->has('parent_account')) {
            $parent = User::where('account', $request->get('parent_account'))->first();
            if (empty($parent)) {
                throw new CustomException('指定的上级代理不存在, 请正确输入上级代理商的账号');
            }

            if ($request->has('group_id')) {
                if ($parent->group_id >= $request->input('group_id')) {
                    //throw new CustomException('新的上级代理商级别应高于此代理商更新之后的级别');
                    throw new CustomException('新的上级代理商级别应高于此代理商的级别');
                }
            } else {
                if ($parent->group_id >= $user->group_id) {
                    throw new CustomException('新的上级代理商级别应高于此代理商的级别');
                }
            }
            $data = array_merge($data, ['parent_id' => $parent->id]);
        }

        $user->update($data);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(), '更新代理商信息',
            $request->header('User-Agent'), json_encode($data));

        return [
            'message' => '更新信息成功'
        ];
    }

    //查询此代理商的下级，与将要更改的组级别作对比，如果将要更改的代理商级别等于或低于其下级代理级别
    public function ifCanChangeGroup(User $user, $groupId)
    {
        $children = User::where('parent_id', $user->id)->get();
        foreach ($children as $child) {
            if ($child->group_id <= $groupId) {
                return false;
            }
        }
        return true;
    }

    public function updatePass(AdminRequest $request, User $user)
    {
        Validator::make($request->all(), [
            'password' => 'required|min:6'
        ])->validate();

        $data = ['password' => bcrypt($request->get('password')) ];

        $user->update($data);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(), '更新代理商密码',
            $request->header('User-Agent'), json_encode($data));

        return [
            'message' => '更新密码成功'
        ];
    }

    //获取已售出的道具（给下级代理商充值的道具 + 给玩家充值的道具）
    public function getItemSoldRecords(AdminRequest $request)
    {
        $this->validate($request, [
            'item_type' => 'required|exists:item_type,id',
            'account' => 'required',
        ]);
        $itemType = $request->input('item_type');

        //打开页面vuetable第一次请求时，直接返回空数据
        if ($request->input('account') === '0') {
            return Paginator::paginate([]);
        }

        $agent = User::with([
            'agentTopUpRecords' => function ($query) use ($itemType) {
                $query->where('type', $itemType);
            },
            'playerTopUpRecords' => function ($query) use ($itemType) {
                $query->where('type', $itemType);
            }])
            ->where('account', $request->input('account'))
            ->first();

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查询售卡记录', $request->header('User-Agent'), $request->all());

        if (empty($agent)) {
            throw new CustomException('代理商不存在');
        }

        //将receiver接收者的信息append到agent_top_up_records里面
        foreach ($agent->agentTopUpRecords as $agentTopUpRecord) {
            $agentTopUpRecord['receiver'] = User::where('id', $agentTopUpRecord['receiver_id'])->first();
        }

        //获取玩家昵称，append到数据集中
        foreach ($agent->playerTopUpRecords as $playerTopUpRecord) {
            $playerTopUpRecord['nick_name'] = PlayerService::getNickName($playerTopUpRecord->player);
        }

        $data = array_merge([], $agent->agentTopUpRecords->toArray(), $agent->playerTopUpRecords->toArray());
        $data = collect($data)->sortByDesc('created_at')->toArray();

        return Paginator::paginate($data, $this->per_page, $this->page);
    }

    //代理商有效耗卡列表
    public function getAgentValidCardConsumedRecord(AdminRequest $request)
    {
        $this->validate($request, [
            'account' => 'required'
        ]);

        //打开页面vuetable第一次请求时，直接返回空数据
        if ($request->input('account') === '0') {
            return Paginator::paginate([]);
        }

        $agent = User::where('account', $request->input('account'))->first();

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查询有效耗卡记录', $request->header('User-Agent'), $request->all());

        if (empty($agent)) {
            throw new CustomException('代理商不存在');
        }

        $data = ValidCardConsumedService::getSpecifiedAgentTopUpLog($agent->id);

        //添加玩家的昵称数据
        foreach ($data as &$item) {
            $item['provider_account'] = $agent->account;
            $item['provider_nickname'] = $agent->name;
            $item['player_nickname'] = PlayerService::getNickName($item['player']);
        }
        return Paginator::paginate($data, $this->per_page, $this->page);
    }
}