<?php

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRequest;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Services\Game\ValidCardConsumedService;

class SubAgentController extends Controller
{
    protected $agentGroups = [2, 3, 4];     //agent代理商的组id号

    /**
     * 查看子代理商列表(带分页)
     *
     * @SWG\Get(
     *     path="/agent/api/subagent",
     *     description="查看子代理商列表(带分页)",
     *     operationId="agent.subagent.get",
     *     tags={"subagent"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/sort",
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/page",
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/per_page",
     *     ),
     *     @SWG\Parameter(
     *         name="filter",
     *         description="搜索子代理商帐号",
     *         in="query",
     *         required=false,
     *         type="string",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回子代理商分页数据",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/User"),
     *             },
     *             @SWG\Property(
     *                 property="inventorys",
     *                 type="array",
     *                 @SWG\Items(
     *                     allOf={
     *                         @SWG\Schema(ref="#/definitions/Inventory"),
     *                     },
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function show(AgentRequest $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看子代理商列表', $request->header('User-Agent'), json_encode($request->all()));

        $data = User::with(['group', 'parent', 'inventorys.item', 'agentTopUpRecords', 'playerTopUpRecords'])
            ->whereIn('group_id', $this->agentGroups)
            ->where('parent_id', $request->user()->id)
            ->when($request->has('filter'), function ($query) use ($request) {
                $filterText = $request->filter;
                return $query->where('account', 'like', "%${filterText}%");
            })
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);

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

    /**
     * 创建子代理商
     *
     * @SWG\Post(
     *     path="/agent/api/subagent",
     *     description="创建子代理商",
     *     operationId="agent.subagent.post",
     *     tags={"subagent"},
     *
     *     @SWG\Parameter(
     *         name="account",
     *         description="登录帐号",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="group_id",
     *         description="代理级别id(2-总代,3-钻石,4-黄金)",
     *         in="formData",
     *         required=true,
     *         type="integer",
     *         enum={2,3,4},
     *         default=2,
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         description="昵称",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         description="密码",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="password_confirmation",
     *         description="再次输入密码",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回创建成功或验证失败",
     *         @SWG\Property(
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Success"),
     *             },
     *         ),
     *     ),
     * )
     */
    public function create(AgentRequest $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'account' => 'required|string|max:190|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'email' => 'string|email|max:255',
            'phone' => 'integer|digits:11',
            'group_id' => 'required|integer|not_in:1,2|exists:groups,id',    //不能创建管理员和总代理
        ])->validate();

        if ($request->user()->is_lowest_agent) {
            throw new CustomException('最下级代理商无法创建子代理商');
        }

        $data = $request->intersect(
            'name', 'account', 'password', 'email', 'phone', 'group_id'
        );
        $data['password'] = bcrypt($data['password']);
        $data = array_merge($data, [ 'parent_id' => $request->user()->id ]);

        User::create($data);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '创建子代理商', $request->header('User-Agent'), json_encode($data));

        return [
            'message' => '创建子代理商成功'
        ];
    }

    //删除下级代理商
    public function destroy(AgentRequest $request, User $user)
    {
        if ($user->is_admin) {
            throw new CustomException('不能删除管理员');
        }

        if ($user->hasChild()) {
            throw new CustomException('此代理商下存在下级代理');
        }

        //检查当前操作的代理商是否是被删除代理商的上级
        if (! $user->isChild(Auth::id())) {
            throw new CustomException('只能删除您自己的子代理商');
        }

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除子代理商', $request->header('User-Agent'), $user->toJson());

        $user->delete();

        return [
            'message' => '删除成功'
        ];
    }

    /**
     * 编辑子代理商的信息
     *
     * @SWG\Put(
     *     path="/agent/api/subagent/{subagent_id}",
     *     description="编辑子代理商的信息",
     *     operationId="agent.subagent.put",
     *     tags={"subagent"},
     *
     *     @SWG\Parameter(
     *         name="subagent_id",
     *         description="子代理商id",
     *         in="path",
     *         required=true,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         description="子代理商昵称",
     *         in="formData",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="account",
     *         description="子代理商帐号",
     *         in="formData",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         description="密码",
     *         in="formData",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         description="子代理商email",
     *         in="formData",
     *         required=false,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="phone",
     *         description="手机",
     *         in="formData",
     *         required=false,
     *         type="string",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回更新成功或验证失败",
     *         @SWG\Property(
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Success"),
     *             },
     *         ),
     *     ),
     * )
     */
    public function updateChild(AgentRequest $request, User $child)
    {
        //检查是否是其子代理商
        if (! $child->isChild(Auth::id())) {
            throw new CustomException('只允许更新属于您自己的下级');
        }

        $input = $request->all();

        //如果提交的用户名和用户本身的用户名相同，就不进行Validate，不然unique验证失败
        if ($request->has('account') and ($request->input('account') == $child->account)) {
            unset($input['account']);
        }

        Validator::make($input, [
            'name' => 'string|max:255',
            'account' => 'string|max:255|unique:users',
            'password' => 'string|min:6',
            'email' => 'string|email|max:255',
            'phone' => 'integer|digits:11',
        ])->validate();

        $data = $request->intersect(
            'name', 'account', 'password', 'email', 'phone'
        );

        if (array_key_exists('password', $data)) {    //如果有传递密码过来
            $data['password'] = bcrypt($data['password']);  //加密密码
        }

        $child->update($data);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '更新子代理商信息', $request->header('User-Agent'), json_encode($data));

        return [
            'message' => '更新信息成功'
        ];
    }
}