<?php

namespace Rikkei\Core\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
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
                return redirect()->guest('/');
            }
        }
        // Check permission
        if (! \Rikkei\Team\View\Permission::getInstance()->isAllow()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => trans('core::message.You don\'t have access')], 401);
            }
            echo view('errors.permission');
            exit;
        }
        return $next($request);
    }

}
