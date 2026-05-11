<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StrictAuditLogging
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Proceed with the request first to get the response status
        $response = $next($request);

        // Only log if the user is authenticated and the setting is strictly enabled
        if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $isStrictLogEnabled = \App\Models\Setting::get('enable_strict_audit_logging', false);

            if ($isStrictLogEnabled) {
                // Avoid logging noise like ajax polling, livewire internal requests, or asset fetching if they somehow pass through web middleware
                if (!$request->ajax() && !$request->is('broadcasting/auth') && !$request->is('admin/logs/stream')) {
                    
                    // Determine the severity based on action
                    $method = $request->method();
                    $actionType = 'READ_ACCESS';
                    $severity = 'info';

                    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                        $actionType = 'WRITE_OPERATION';
                        $severity = 'warning';
                    }

                    $path = $request->path();
                    $role = auth()->user()->is_admin ? 'Admin' : 'Personnel';

                    \App\Models\SystemLog::create([
                        'user_id' => auth()->id(),
                        'event_type' => 'AUDIT',
                        'action' => "STRICT_AUDIT: {$actionType}",
                        'description' => "{$role} performed a {$method} request on /{$path}.",
                        'severity' => $severity,
                        'ip_address' => $request->ip()
                    ]);
                }
            }
        }

        return $response;
    }
}
