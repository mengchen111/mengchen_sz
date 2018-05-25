<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        AuthenticatesUsers::login as doLogin;
        AuthenticatesUsers::logout as doLogout;
    }

    /**
     * Where to redirect users after login.
     * 如果存在redirectTo方法，那么redirectTo方法的返回值会覆盖此值
     *
     * @var string
     */
    protected $redirectTo = '/admin/home';

    protected $adminHomePath = '/admin/home';
    protected $agentHomePath = '/agent/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'account';
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //使用$request->user()获取登录用户信息
        //$request->session()->put('user', $user);
        //$request->session()->put('group', $user->group);
    }

    protected function redirectTo()
    {
        return Auth::user()->is_agent ? $this->agentHomePath : $this->adminHomePath;
    }

    protected function credentials(Request $request)
    {
        return array_merge($request->only($this->username(), 'password'), ['active' => 1]);
    }

    /**
     *
     * @SWG\Post(
     *     path="/login",
     *     description="登录",
     *     operationId="login",
     *     tags={"login"},
     *     consumes={"application/x-www-form-urlencoded"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="account",
     *         description="帐号",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         description="密码",
     *         in="formData",
     *         required=true,
     *         type="string",
     *     ),
     *     @SWG\Parameter(
     *         name="remember",
     *         description="记住我",
     *         in="formData",
     *         required=true,
     *         type="boolean",
     *         default=true,
     *         enum={true, false},
     *     ),
     *
     *     @SWG\Response(
     *         response=301,
     *         description="登录成功，跳转主页",
     *     ),
     * )
     */
    public function login(Request $request)
    {
        $this->doLogin($request);
    }

    /**
     *
     * @SWG\Post(
     *     path="/logout",
     *     description="登出",
     *     operationId="logout",
     *     tags={"login"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="logout success",
     *     ),
     * )
     */
    public function logout(Request $request)
    {
        $this->doLogout($request);
    }
}
