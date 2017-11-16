<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorizationMap;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthorizationController extends Controller
{
    use AuthorizationMap;

    protected $adminGroupId = '1';

    //显示某个组可以访问的页面
    public function showViewAccess(AdminRequest $request, $group)
    {
        $group = (string) $group === '0' ? Auth::user()->group : Group::find($group);

        return [
            'view_access' => json_decode($group->view_access, true),
            'is_admin' => (string) $group->id === $this->adminGroupId,
        ];
    }

    //设置组权限（可以访问的页面）
    public function setupViewAccess(AdminRequest $request)
    {
        $viewAccess = $this->filterViewAccessData($request);

        $group = Group::find($request->input('gid'));
        $group->update($viewAccess);

        return [
            'message' => '设置组权限成功',
        ];
    }

    protected function filterViewAccessData($request)
    {
        $this->validate($request, [
            'gid' => 'required|exists:groups,id',
            'view_access' => 'required',
        ]);
        return $request->only('view_access');
    }
}
