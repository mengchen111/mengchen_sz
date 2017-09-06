<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/3/17
 * Time: 00:10
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    //管理员更新密码
    public function updatePass(Request $request)
    {
        Validator::make($request->all(), [
            'password' => 'required|min:6',
            'new_password' => 'required|min:6|confirmed',
        ])->validate();

        $user = User::find($request->user()->id);
        if (! Hash::check($request->password, $user->password)) {
            return [
                'error' => '原密码输入错误',
            ];
        }

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '更新密码', $request->header('User-Agent'));
        return $user->update([
            'password' => bcrypt($request->new_password)
        ]) ? [
            'message' => '更新密码成功'
        ] : [
            'error' => '更新密码失败'
        ];
    }
}