<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\AuthorizationMap;
use App\Models\Group;
use App\Models\GroupIdMap;
use App\Services\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;

class GroupController extends Controller
{
    use GroupIdMap;
    use AuthorizationMap;

    public function show(AdminRequest $request)
    {
        $groups =  Group::whereNotIn('id', $this->agentGids)
            ->get()
            ->map(function ($value) {
                unset($value['view_access']);
                return $value;
            })
            ->toArray();
        krsort($groups);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看组列表', $request->header('User-Agent'), json_encode($request->all()));

        return Paginator::paginate($groups, $this->per_page, $this->page);
    }

    public function create(AdminRequest $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:groups,name',
        ]);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '创建组', $request->header('User-Agent'), json_encode($request->all()));

        Group::create([
            'name' => $request->input('name'),
            'view_access' => json_encode($this->initGroupView),
        ]);

        return [
            'message' => '创建组成功'
        ];
    }

    public function edit(AdminRequest $request, Group $group)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '编辑组名', $request->header('User-Agent'), json_encode($request->all()));

        if ($group->is_admin_group) {
            throw new CustomException('管理员组禁止编辑');
        }

        if ($group->name === $request->input('name')) {
            throw new CustomException('新组名与原组名一致, 数据未更新');
        }

        $this->validate($request, [
            'name' => 'required|string|max:255|unique:groups,name',
        ]);

        $group->update([
            'name' => $request->input('name')
        ]);

        return [
            'message' => '更新组信息成功',
        ];
    }

    public function destroy(AdminRequest $request, Group $group)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '删除组', $request->header('User-Agent'), json_encode($request->all()));

        if ($group->is_admin_group) {
            throw new CustomException('管理员组禁止删除');
        }

        if ($group->hasMember()) {
            return [
                'error' => '禁止删除非空组, 请先清空此组下的成员再尝试删除'
            ];
        }

        $group->delete();
        return [
            'message' => '删除组成功',
        ];
    }

    public function showMap(AdminRequest $request)
    {
        $groupMap = Group::whereNotIn('id', $this->agentGids)
            ->get()
            ->pluck('name', 'id');
        unset($groupMap[$this->adminGid]);
        return $groupMap;
    }
}
