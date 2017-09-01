<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\Validator;

class SubAgentController extends Controller
{
    //查看下级代理商列表
    public function show(Request $request)
    {
        $per_page = $request->per_page ?: 10;
        $order = $request->sort ? explode('|', $request->sort) : ['id', 'desc'];

        OperationLogs::add(session('user')->id, $request->path(), $request->method(),
            '查看子代理商列表', $request->header('User-Agent'));

        if ($request->has('filter')) {
            $filterText = $request->filter;
            return User::with(['group', 'parent', 'inventorys.item'])
                ->where('account', 'like', "%{$filterText}%")
                ->where('parent_id', session('user')->id)
                ->orderBy($order[0], $order[1])
                ->paginate($per_page);
        }
        return User::with(['group', 'parent', 'inventorys.item'])
            ->where('parent_id', session('user')->id)
            ->orderBy($order[0], $order[1])
            ->paginate($per_page);
    }

    //创建下级代理商
    public function create(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'account' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'email' => 'string|email|max:255',
            'phone' => 'integer|digits:11',
            //'group_id' => 'required|integer|not_in:1,2',    //不能创建管理员和总代理
        ])->validate();

        if (session('user')->is_lowest_agent) {
            return [
                'error' => '最下级代理商无法创建子代理商'
            ];
        }

        $data = $request->intersect(
            'name', 'account', 'password', 'email', 'phone'
        );
        $data['password'] = bcrypt($data['password']);
        $data = array_merge($data, [ 'parent_id' => session('user')->id ]);
        $data['group_id'] = session('user')->group_id + 1;    //下级代理商组id

        OperationLogs::add(session('user')->id, $request->path(), $request->method(),
            '创建子代理商', $request->header('User-Agent'), json_encode($data));
        return User::create($data);
    }

    //删除下级代理商
    public function destroy(Request $request, User $user)
    {
        if ($this->isAdmin($user)) {
            return [
                'error' => '不能删除管理员'
            ];
        }

        if ($this->hasSubAgent($user)) {
            return [
                'error' => '此代理商下存在下级代理'
            ];
        }

        OperationLogs::add(session('user')->id, $request->path(), $request->method(),
            '删除子代理商', $request->header('User-Agent'), $user->toJson());

        return $user->delete() ? ['message' => '删除成功'] : ['error' => '删除失败'];
    }

    protected function isAdmin($user)
    {
        return 1 == $user->group_id;
    }

    protected function hasSubAgent($user)
    {
        return User::where('parent_id', $user->id)->get()->count();
    }

    //代理商更改自己的个人信息
    public function update(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'string|max:255',
            'password' => 'string|min:6',
            'email' => 'string|email|max:255',
            'phone' => 'integer|digits:11',
        ])->validate();

        $data = $request->intersect(
            'name', 'password', 'email', 'phone'
        );

        if (session('user')->update($data)) {
            //TODO 操作日志记录
            return [
                'message' => '更新用户数据成功'
            ];
        }
    }

    //代理商更新其子代理商的信息
    public function updateChild(Request $request, User $child)
    {
        //检查是否是其子代理商
        if (! $this->isChild($child)) {
            return [
                'error' => '只允许更新属于您自己的下级'
            ];
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

        if ($child->update($data)) {
            OperationLogs::add(session('user')->id, $request->path(), $request->method(),
                '更新子代理商信息', $request->header('User-Agent'), json_encode($data));

            return [
                'message' => '更新用户数据成功'
            ];
        }
    }

    protected function isChild($child)
    {
        return session('user')->id === $child->parent_id;
    }
}