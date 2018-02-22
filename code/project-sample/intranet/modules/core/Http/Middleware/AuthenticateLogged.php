<?php

namespace Rikkei\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Session;
use Rikkei\Core\Http\Middleware\Authenticate;

class AuthenticateLogged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                Session::put('curUrl',$request->fullUrl());
                return redirect()->guest('/');
            }
        }
        return $next($request);
    }
}
