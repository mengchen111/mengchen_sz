<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RollController extends Controller
{
    public function show(AdminRequest $request)
    {
        //TODO 列出所有角色（除了agent角色）
    }

    public function create(AdminRequest $request)
    {
        //TODO 创建角色
    }

    public function edit(AdminRequest $request)
    {
        //TODO 编辑角色
    }

    public function destroy(AdminRequest $request)
    {
        //TODO 删除角色
    }
}
