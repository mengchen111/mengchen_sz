<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
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

        //查找用户名
        if ($request->has('filter')) {
            $filterText = $request->filter;
            $data = User::with(['group', 'parent', 'inventorys.item', 'agentTopUpRecords', 'playerTopUpRecords'])
                ->where('account', 'like', "%{$filterText}%")
                ->whereIn('group_id', $this->agentGroups)
                ->orderBy($order[0], $order[1])
                ->paginate($per_page);
        } else {
            $data = User::with(['group', 'parent', 'inventorys.item', 'agentTopUpRecords', 'playerTopUpRecords'])
                ->whereIn('group_id', $this->agentGroups)
                ->orderBy($order[0], $order[1])     //不允许查看管理员
                ->paginate($per_page);
        }

        return $this->buildData4ItemSoldCount($data);
    }

    //计算代理商的总售卡数
    public function buildData4ItemSoldCount($data)
    {
        foreach ($data->items() as $user) {
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
            'group_id' => 'integer|not_in:1',   //不能将代理商改成管理员
            //'parent_account' => 'string|exists:users,account',
        ])->validate();

        $data = $request->intersect(
            'name', 'account', 'email', 'phone', 'group_id'
        );

        //暂不支持改上级
        /*if ($request->has('parent_account')) {
            $parentId = User::where('account', $request->get('parent_account'))->first()->id;
            $data = array_merge($data, ['parent_id' => $parentId]);
        }*/

        $user->update($data);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(), '更新代理商信息',
            $request->header('User-Agent'), json_encode($data));

        return [
            'message' => '更新信息成功'
        ];
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
}