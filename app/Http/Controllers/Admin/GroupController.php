<?php

namespace App\Http\Controllers\Admin;

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
        //TODO 编辑组名(管理员组不允许编辑)
    }

    public function destroy(AdminRequest $request, Group $group)
    {
        //TODO 删除组（非空不能删）
    }
}
