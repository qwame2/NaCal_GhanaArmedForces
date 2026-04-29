<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginBypass
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            $user = User::first();
            if (!$user) {
                $user = User::create([
                    'name' => 'System Administrator',
                    'username' => 'admin',
                    'password' => Hash::make('password'),
                ]);
            }
            Auth::login($user);
        }

        return $next($request);
    }
}
