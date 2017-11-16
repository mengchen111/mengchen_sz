<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    protected $adminHomePath = '/admin/home';
    protected $agentHomePath = '/agent/home';

    /**
     * Handle an incoming request.
     * 这里只有的登录控制器调用完成之后才会调用此处理器，已经登录过的不经过它
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return $request->user()->is_agent
                ? redirect($this->agentHomePath)
                : redirect($this->adminHomePath);
        }

        return $next($request);
    }
}
