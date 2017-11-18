<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CustomException;
use App\Http\Requests\AdminRequest;
use App\Models\Group;
use App\Models\GroupIdMap;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorizationMap;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthorizationController extends Controller
{
    use AuthorizationMap;
    use GroupIdMap;

    protected $viewAccess = [];

    //显示某个组可以访问的页面
    public function showViewAccess(AdminRequest $request, $group)
    {
        //传0过来则查询当前登录用的组
        $group = (string) $group === '0' ? Auth::user()->group : Group::find($group);

        return [
            'view_access' => $group->view_access,
            'is_admin' => (string) $group->id === $this->adminGid,
        ];
    }

    //设置组权限（可以访问的页面）
    public function setupViewAccess(AdminRequest $request, Group $group)
    {
        $viewAccess = $this->filterViewAccessData($request);

        $group->update([
            'view_access' => json_encode($viewAccess),
        ]);
        
        return [
            'message' => '设置组权限成功',
        ];
    }

    protected function filterViewAccessData($request)
    {
        $this->validate($request, [
            'view_access' => 'required',
        ]);

        $this->viewAccess = json_decode($request->input('view_access'), true);
        if (empty($this->viewAccess)) {
            throw new CustomException('view_access数据错误');
        }
        $this->formatViewAccess();
        return $this->viewAccess;
    }

    //格式化数据，如果下级的ifShown为true，那么将上级菜单的ifShown设置为true
    protected function formatViewAccess()
    {
        foreach ($this->viewAccess as $topLevel => &$secondLevel) {
            $foo = $secondLevel;
            unset($foo['ifShown']);
            if (empty($foo)) {
                continue;
            }
            $this->viewAccess[$topLevel]['ifShown'] = $this->iterateFormat($secondLevel);
        }
    }

    protected function iterateFormat(& $arr)
    {
        $ifShown = false;
        foreach ($arr as $upperLever => &$lowerLevel) {
            if ($upperLever === 'ifShown') {
                continue;
            }
            $foo = $lowerLevel;
            unset($foo['ifShown']);
            if (! empty($foo)) {
                if ($arr[$upperLever]['ifShown'] === true) {
                    continue;
                }
                unset($lowerLevel['ifShown']);
                if ($this->iterateFormat($lowerLevel)) {
                    $arr['ifShown'] = true;
                    $arr[$upperLever]['ifShown'] = true;
                    return true;
                }
            } else {
                $ifShown = $lowerLevel['ifShown'];
                if ($ifShown) {
                    return true;
                } else {
                    continue;
                }
            }
        }
        return $ifShown;
    }
}
