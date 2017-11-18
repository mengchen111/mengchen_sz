<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use App\Models\GroupIdMap;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OperationLogs;

class RoleController extends Controller
{
    use GroupIdMap;

    public function show(AdminRequest $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看角色列表', $request->header('User-Agent'), json_encode($request->all()));

        $groupId = $request->input('group_id');

        return User::with('group')
            ->whereNotIn('group_id', $this->agentGids)
            ->when($groupId, function ($query) use ($groupId) {
                return $query->where('group_id', $groupId);
            })
            ->orderBy('id', 'desc')
            ->paginate($this->per_page);
    }

    public function create(AdminRequest $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '创建角色', $request->header('User-Agent'), json_encode($request->all()));

        $data = $this->filterCreateData($request);

        User::create($data);
        return [
            'message' => '角色创建成功'
        ];
    }

    protected function filterCreateData($request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'account' => 'required|string|max:255|unique:users,account',
            'group_id' => 'required|integer|exists:groups,id|not_in:' . $this->adminGid . ',' . implode(',', $this->agentGids),
            'password' => 'required|string|min:6|max:32|confirmed',
        ]);
        $data = $request->only(['name', 'account', 'group_id', 'password']);
        $data['parent_id'] = $this->adminId;
        $data['password'] = bcrypt($data['password']);
        return $data;
    }

    public function edit(AdminRequest $request, User $role)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '编辑角色', $request->header('User-Agent'), json_encode($request->all()));

        $data = $this->filterEditData($request, $role);

        $role->update($data);
        return [
            'message' => '更新角色信息成功'
        ];
    }

    protected function filterEditData($request, $role)
    {
        if ($role->is_admin or $role->is_agent) {
            throw new CustomException('禁止编辑管理员或代理商');
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'account' => 'required|string|max:255',
            'group_id' => 'required|integer|exists:groups,id|not_in:' . $this->adminGid . ',' . implode(',', $this->agentGids),
            'password' => 'string|min:6|max:32'
        ]);

        //检查输入的account是否被其他用户占用
        $user = User::where('account', $request->input('account'))->first();
        if (! empty($user) and (string) $user->id !== (string) $role->id) {
            throw new CustomException('此用户名已被占用');
        }

        $data = $request->intersect(['name', 'account', 'group_id']);

        if ($request->has('password')) {
            $data['password'] = bcrypt($request->input('password'));
        }

        return $data;
    }

    public function destroy(AdminRequest $request, User $role)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除角色', $request->header('User-Agent'), json_encode($request->all()));

        if ($role->is_admin or $role->is_agent) {
            throw new CustomException('禁止删除管理员或代理商');
        }

        $role->delete();
        return [
            'message' => '删除角色成功',
        ];
    }
}
