<?php

namespace Rikkei\Core\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Auth;

class CheckPermission
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
        // Get the required roles from the route
        $roles = $this->getRequiredRolesForRoute($request->route());

        // Check if a role is required for the route, and
        // if so, ensure that the user has that role.
        if (!empty($roles)) {



            return $next($request);
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * Get list allow roles
     *
     * @param \Illuminate\Routing\Route $route
     * @return array
     */
    private function getRequiredRolesForRoute($route)
    {
        $actions = $route->getAction();
        return isset($actions['roles']) ? $actions['roles'] : [];
    }

    /**
     * Get list user's roles
     *
     * @return array
     */
    private function getUserRoles()
    {
        // TODO:
        return [];
    }
}