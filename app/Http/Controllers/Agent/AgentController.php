<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/3/17
 * Time: 12:39
 */

namespace App\Http\Controllers\Agent;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AgentRequest;

class AgentController extends Controller
{
    /**
     * 代理商更改密码
     *
     * @SWG\Put(
     *     path="/agent/api/self/password",
     *     description="更改密码",
     *     operationId="agent.self.password.put",
     *     tags={"user"},
     *
     *     @SWG\Parameter(
     *         name="password",
     *         description="原密码",
     *         in="formData",
     *         required=true,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="new_password",
     *         description="新密码",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="new_password_confirmation",
     *         description="再次输入新密码",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="返回更新密码成功或验证失败",
     *         @SWG\Property(
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/Success"),
     *             },
     *         ),
     *     ),
     *
     *     @SWG\Response(
     *         response=422,
     *         description="参数验证错误",
     *         @SWG\Property(
     *             type="object",
     *             allOf={
     *                 @SWG\Schema(ref="#/definitions/ValidationError"),
     *             },
     *         ),
     *     ),
     * )
     */
    public function updatePass(AgentRequest $request)
    {
        Validator::make($request->all(), [
            'password' => 'required|min:6',
            'new_password' => 'required|min:6|confirmed',
        ])->validate();

        $user = Auth::user();
        if (! Hash::check($request->password, $user->password)) {
            throw new CustomException('原密码输入错误');
        }

        OperationLogs::add($user->id, $request->path(), $request->method(),
            '更新密码', $request->header('User-Agent'));

        $user->update([
            'password' => bcrypt($request->new_password)
        ]);

        return [
            'message' => '更新密码成功'
        ];
    }

    //代理商更改自己的个人信息
    public function update(AgentRequest $request)
    {
        Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255',
            'phone' => 'integer|digits:11',
        ])->validate();

        $data = $request->intersect(
            'name', 'email', 'phone'
        );

        Auth::user()->update($data);

        OperationLogs::add(Auth::id(), $request->path(), $request->method(),
            '更新代理商个人信息', $request->header('User-Agent'), json_encode($data));

        return [
            'message' => '更新用户数据成功'
        ];
    }

    //获取个人的代理级别类型
    public function agentType(AgentRequest $request)
    {
        return Auth::user()->group;
    }
}