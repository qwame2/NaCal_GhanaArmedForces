<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureNotTempAccount
 * 
 * Restricts temporary requisitioner accounts exclusively to the requisitions page.
 * Any attempt to navigate elsewhere is silently redirected back to the requisition form.
 */
class EnsureNotTempAccount
{
    /**
     * Routes that temporary accounts ARE allowed to access.
     * These must match route names or URI prefixes.
     */
    protected array $allowedRoutes = [
        'requisitions.index',
        'requisitions.store',
        'requisitions.checkout',
        'requisitions.my',
        'requisitions.history',
        'api.my-requisitions',
        'api.unit-rules',
        'api.user.permissions',
        'api.total-unread',
        'api.unread-counts',
        'api.online-statuses',
        'api.user.offline',
        'settings.avatar',
        'settings.update',
        'settings.password',
        'logout',
        'password.change',
        'password.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_temp_account) {
            if (!Auth::user()->is_active) {
                $user = Auth::user();
                $user->update(['is_online' => false]);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('error', 'Access Suspended: Your department currently has overdue temporary assets. Active temporary accounts are suspended.');
            }
            $routeName = $request->route()?->getName();

            // Allow explicitly permitted named routes
            if ($routeName && in_array($routeName, $this->allowedRoutes)) {
                return $next($request);
            }

            // Allow URI prefixes for requisition-related paths
            $path = $request->path();
            $allowedPrefixes = [
                'requisitions',
                'api/my-requisitions',
                'api/unit-rules',
                'api/user/permissions',
                'api/total-unread',
                'api/unread-counts',
                'api/online-statuses',
                'api/user/offline',
                'logout',
                'password/change',
                'settings/avatar',
                'settings/update',
                'settings/password',
            ];

            foreach ($allowedPrefixes as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    return $next($request);
                }
            }

            // Redirect all other routes to the requisition page
            return redirect()->route('requisitions.index');
        }

        return $next($request);
    }
}
