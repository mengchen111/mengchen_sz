<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use App\Models\RebateRule;
use App\Http\Controllers\Controller;

class RebateRuleController extends Controller
{
    public function index()
    {
        $this->addLog('查看返利规则');

        return RebateRule::paginate();
    }

    public function store(AdminRequest $request)
    {
        $this->validator($request);
        $this->addLog('添加返利规则');

        $result = RebateRule::create($request->all());
        return [
            'message' => '添加返利规则' . ($result ? '成功' : '失败'),
        ];
    }

    public function update(AdminRequest $request, RebateRule $rule)
    {
        $this->validator($request);
        $this->addLog('修改返利规则');

        $result = $rule->update($request->all());
        return [
            'message' => '修改返利规则' . ($result ? '成功' : '失败'),
        ];
    }

    public function destroy(AdminRequest $request, RebateRule $rule)
    {
        $this->addLog('删除返利规则');

        $result = $rule->delete();
        return [
            'message' => '删除返利规则' . ($result ? '成功' : '失败'),
        ];
    }

    protected function validator(AdminRequest $request)
    {
        $this->validate($request, [
            'price' => 'required|integer',
            'rate' => 'required',
            'remark' => 'required',
        ]);
    }
}
