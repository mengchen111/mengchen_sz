<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class SubAgentController extends Controller
{
    protected $currentAgent = [];

    public function __construct(Request $request)
    {
        //TODO 登录功能完成之后更改
        $this->currentAgent = session('user') ? session('user') : User::find(2);
    }

    //查看下级代理商列表
    public function show()
    {
        //TODO 记录日志
        return User::where('parent_id', $this->currentAgent->id)->get();
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
            'group_id' => 'required|integer|not_in:1,2',    //不能创建管理员和总代理
        ])->validate();

        $data = $request->intersect(
            'name', 'account', 'password', 'email', 'phone', 'group_id'
        );
        $data = array_merge($data, [ 'parent_id' => $this->currentAgent->id ]);
        //TODO 记录日志
        return User::create($data);
    }

    //删除下级代理商
    public function destroy(User $user)
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

        //TODO 记录日志
        return $user->delete() ? ['message' => '删除成功'] : ['message' => '删除失败'];
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
    protected function update(Request $request)
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

        if ($this->currentAgent->update($data)) {
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

        if ($child->update($data)) {
            //TODO 操作日志记录
            return [
                'message' => '更新用户数据成功'
            ];
        }
    }

    protected function isChild($child)
    {
        return $this->currentAgent->id === $child->parent_id;
    }
}