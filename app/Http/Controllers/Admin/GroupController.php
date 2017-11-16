<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{
    public function show(AdminRequest $request)
    {
        return Group::all();
    }

    public function create(AdminRequest $request)
    {
        //TODO 创建组
    }

    public function edit(AdminRequest $request)
    {
        //TODO 编辑组名(管理员组不允许编辑)
    }

    public function destroy(AdminRequest $request)
    {
        //TODO 删除组（非空不能删）
    }
}
