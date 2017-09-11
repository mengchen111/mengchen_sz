<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/3/17
 * Time: 00:10
 */

namespace App\Http\Controllers\Admin;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Models\OperationLogs;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    //管理员更新密码
    public function updatePass(AdminRequest $request)
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
}