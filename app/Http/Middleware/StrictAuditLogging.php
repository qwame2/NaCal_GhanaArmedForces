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

        // Log all personnel activity by default (Always On)
        if (auth()->check()) {
            // Avoid logging noise like ajax polling, livewire internal requests, or asset fetching
            if (!$request->ajax() && !$request->is('broadcasting/auth') && !$request->is('admin/logs/stream')) {
                
                // Determine the severity based on action
                    $method = $request->method();
                    $actionType = 'VIEWED PAGE';
                    $severity = 'info';

                    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                        $actionType = 'CHANGED DATA';
                        $severity = 'warning';
                    }

                    $path = $request->path();
                    $role = auth()->user()->is_admin ? 'Admin' : 'Personnel';

                    // Human-friendly path mapping
                    $friendlyPath = '/' . $path;
                    $mappings = [
                        'dashboard' => 'the Dashboard',
                        'admin/logs' => 'System Logs',
                        'admin/inventory' => 'the Inventory Overview',
                        'admin/users' => 'the User Registry',
                        'admin' => 'the Admin Hub',
                        'received-items' => 'the Received Items list',
                        'issue-items' => 'the Item Issuance console',
                        'return-items' => 'the Item Recovery page',
                        'stock-check' => 'the Stock Verification page',
                        'api/total-unread' => 'notifications',
                        'messages' => 'messages',
                        'profile' => 'profile settings',
                    ];

                    foreach ($mappings as $slug => $readable) {
                        if ($path === $slug || str_starts_with($path, $slug . '/')) {
                            $friendlyPath = $readable;
                            break;
                        }
                    }

                    // Friendly verb mapping
                    if ($method === 'GET') {
                        $description = "{$role} " . ($path === 'api/total-unread' ? 'checked for' : 'viewed') . " {$friendlyPath}.";
                    } else {
                        $action = 'interacted with';
                        if ($method === 'POST') $action = 'submitted data to';
                        if ($method === 'DELETE') $action = 'removed data from';
                        if ($method === 'PATCH' || $method === 'PUT') $action = 'updated';
                        $description = "{$role} {$action} {$friendlyPath}.";
                    }

                    \App\Models\SystemLog::create([
                        'user_id' => auth()->id(),
                        'event_type' => 'ACTIVITY',
                        'action' => $actionType,
                        'description' => $description,
                        'severity' => $severity,
                        'ip_address' => $request->ip()
                    ]);
            }
        }

        return $response;
    }
}
