<?php

namespace App\Http\Middleware;

use Closure;

class CheckAgentActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->active === 0){
            auth()->logout();
            return redirect('login');
        }
        return $next($request);
    }
}
