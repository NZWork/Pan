<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class Login
{
    /**
     * 中间件 登入校验
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function handle($request, Closure $next)
    {
        $session = Session::get('user');
        if (empty($session)) {
            return redirect('/login');
        }
        return $next($request);
    }
}
