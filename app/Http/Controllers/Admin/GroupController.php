<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\AuthorizationMap;
use App\Models\Group;
use App\Models\GroupIdMap;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{
    use GroupIdMap;
    use AuthorizationMap;

    public function show(AdminRequest $request)
    {
        return Group::whereNotIn('id', $this->agentGids)
            ->get()
            ->map(function ($value) {
                unset($value['view_access']);
                return $value;
            });
    }

    public function create(AdminRequest $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        return Group::create([
            'name' => $request->input('name'),
            'view_access' => json_encode($this->initGroupView),
        ]);
    }

    public function edit(AdminRequest $request, Group $group)
    {
        if ($group->is_admin_group) {
            throw new CustomException('管理员组禁止编辑');
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
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
}
