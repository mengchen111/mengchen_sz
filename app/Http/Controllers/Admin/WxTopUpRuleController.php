<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminRequest;
use App\Http\Controllers\Controller;
use App\Models\WxTopUpRule;

class WxTopUpRuleController extends Controller
{
    public function index()
    {
        $this->addLog('查看微信充值规则');

        return WxTopUpRule::paginate($this->per_page);
    }

    public function store(AdminRequest $request)
    {
        $this->validator($request);
        $this->addLog('添加微信充值规则');

        $result = WxTopUpRule::create($request->all());
        return [
            'message' => '添加微信充值规则' . ($result ? '成功' : '失败'),
        ];
    }

    public function update(AdminRequest $request, WxTopUpRule $rule)
    {
        $this->validator($request);
        $this->addLog('修改微信充值规则');

        $result = $rule->update($request->all());
        return [
             'message' => '修改微信充值规则' . ($result ? '成功' : '失败'),
        ];
    }

    public function destroy(AdminRequest $request, WxTopUpRule $rule)
    {
        $this->addLog('删除微信充值规则');

        $result = $rule->delete();
        return [
            'message' => '删除微信充值规则' . ($result ? '成功' : '失败'),
        ];
    }

    protected function validator(AdminRequest $request)
    {
        $this->validate($request, [
            'amount' => 'required|integer',
            'give' => 'required|integer',
            'first_give' => 'required',
            'price' => 'required|integer',
            'remark' => 'required',
        ]);
    }
}
