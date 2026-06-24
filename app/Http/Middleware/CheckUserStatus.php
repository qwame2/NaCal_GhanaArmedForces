<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Only block if explicitly set to false and the user is NOT an admin
            // This prevents accidental lockouts for existing users or admins
            if ($user->is_active === false && !$user->is_admin) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('account.deactivated');
            }

            // Sync online status for active authenticated requests
            if (!$user->is_online) {
                $user->update(['is_online' => true]);
            }
        }

        return $next($request);
    }
}
