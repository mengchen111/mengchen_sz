<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use App\Models\CommunityList;

class CommunityController extends Controller
{
    public function showCommunityList(AdminRequest $request)
    {
        $admin = $request->user();
        OperationLogs::add($admin->id, $request->path(), $request->method(),
            '查看牌艺馆列表', $request->header('User-Agent'));

        return CommunityList::with(['ownerAgent'])
            ->when($request->has('status'), function ($query) use ($request) {
                if ((int)$request->input('status') === 3) {     //返回所有状态的社区列表
                    return $query;
                }
                return $query->where('status', $request->input('status'));
            })
            //查找指定的社区
            ->when($request->has('community_id'), function ($query) use ($request) {
                return $query->where('id', $request->input('community_id'));
            })
            ->orderBy('id', 'desc')
            ->paginate($this->per_page);
    }

    public function approveCommunityApplication(AdminRequest $request)
    {
        $this->validate($request, [
            'community_id' => 'required|integer|exists:community_list,id',
            'status' => 'required|integer|in:1,2',
        ]);
        $formData = $request->only(['community_id', 'status']);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '审批牌艺馆', $request->header('User-Agent'));

        CommunityList::where('id', $formData['community_id'])
            ->update(['status' => $formData['status']]);

        return [
            'message' => '操作成功'
        ];
    }
}
