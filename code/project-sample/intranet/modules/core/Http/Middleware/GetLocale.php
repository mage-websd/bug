<?php

namespace Rikkei\Core\Http\Middleware;

use Closure;
use Config;
use Session;
use App;

class GetLocale
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
        if (!Session::has('app.locale')) {
            $locales = array_keys(Config::get('app.locales'));
            $locale = $request->getPreferredLanguage($locales);
            Session::put('app.locale', is_null($locale) ? Config::get('app.locale') : $locale);
        }

        App::setLocale(Session::get('app.locale'));

        return $next($request);
    }
}