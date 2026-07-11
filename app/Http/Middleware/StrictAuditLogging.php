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
            if (!$request->ajax() && 
                !$request->is('broadcasting/*') && 
                !$request->is('api/*') &&
                !$request->is('admin/logs/stream')) {
                
                // Determine the severity based on action
                    $method = $request->method();
                    $actionType = 'VIEWED PAGE';
                    $severity = 'info';

                    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                        $actionType = 'CHANGED DATA';
                        $severity = 'warning';
                    }

                    $path = $request->path();
                    $path = $request->path();
                    $user = auth()->user();
                    if ($user->isMainAdminOrSub()) {
                        $role = $user->role === 'Main Admin' ? 'Head of Admin(Authorizer)' : $user->getRoleDisplayLabel();
                    } else {
                        $role = $user->is_admin ? ($user->role ?? 'Head of Stores') : 'Staff Member';
                    }

                    // Human-friendly path mapping
                    $friendlyPath = '/' . $path;
                    $mappings = [
                        'dashboard' => 'the Dashboard',
                        'admin/logs' => 'System Logs',
                        'admin/inventory' => 'the Inventory Overview',
                        'admin/users' => 'the User Registry',
                        'admin/requisitions' => 'the Admin Requisitions page',
                        'admin' => 'the Admin Hub',
                        'received-items' => 'the Received Items list',
                        'issue-items' => 'the Item Issuance console',
                        'return-items' => 'the Item Recovery page',
                        'stock-check' => 'the Stock Verification page',
                        'personnel/requisitions' => 'the Store Requisitions Management portal',
                        'requisitions/history' => 'their Requisition History page',
                        'requisitions/checkout' => 'the Requisition Checkout form',
                        'requisitions' => 'the Store Requisitions page',
                        'reports' => 'the Reports center',
                        'settings' => 'the Account Settings panel',
                        'notifications' => 'the Notifications page',
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

                    // Fallback to turn any unmatched raw path like "/personnel/requisitions" into a friendly sentence
                    if (str_starts_with($friendlyPath, '/')) {
                        $cleaned = str_replace(['/', '-', '_'], ' ', ltrim($friendlyPath, '/'));
                        $friendlyPath = 'the ' . ucwords($cleaned) . ' page';
                    }

                    // Custom Overrides for Specific System Endpoints
                    $customDescriptions = [
                        'api/user/offline' => "{$role} securely severed their active network connection.",
                        'api/user/online'  => "{$role} synchronized their active network presence.",
                        'logout'           => "{$role} securely terminated their active session."
                    ];

                    if (isset($customDescriptions[$path])) {
                        $description = $customDescriptions[$path];
                    } else {
                        // Detailed override for Admin Hub actions
                        $adminDetail = null;
                        if (str_starts_with($path, 'admin/') || $path === 'admin') {
                            $adminDetail = $this->getDetailedAdminAction($request, $role, $path, $method);
                        }

                        // Detailed override for SRA actions
                        $sraDetail = $this->getDetailedSraAction($request, $role, $path, $method);

                        if ($adminDetail) {
                            $description = $adminDetail;
                        } elseif ($sraDetail) {
                            $description = $sraDetail;
                        } else {
                            // Friendly verb mapping
                            if ($method === 'GET') {
                                $description = "{$role} " . ($path === 'api/total-unread' ? 'checked for' : 'viewed') . " {$friendlyPath}.";
                            } else {
                                $action = 'interacted with';
                                if ($method === 'POST') $action = 'updated';
                                if ($method === 'DELETE') $action = 'deleted an item from';
                                if ($method === 'PATCH' || $method === 'PUT') $action = 'modified';
                                $description = "{$role} {$action} {$friendlyPath}.";
                            }
                        }
                    }

                    $description = str_replace(['Main Admin', 'Head of Admin'], 'Head of Admin(Authorizer)', $description);

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

    private function getDetailedSraAction($request, $role, $path, $method)
    {
        if (in_array($method, ['GET', 'HEAD'])) {
            return null;
        }

        if ($path === 'service-sra' && $method === 'POST') {
            return "{$role} created a new Service SRA (the document confirming that new items were received into the store).";
        }

        if (preg_match('#^(?:admin|stores|personnel)?/?service-sra/(\d+)(?:/process)?$#', $path, $matches)) {
            $sraId = $matches[1];
            $sra = \App\Models\ServiceSra::find($sraId);
            $sraNum = $sra ? $sra->sra_number : "#{$sraId}";
            
            $actionWord = 'updated';
            if (str_contains($path, '/process')) {
                $actionWord = $request->input('action') === 'approved' ? 'approved' : ($request->input('action') === 'declined' ? 'declined' : 'processed');
            } elseif ($method === 'DELETE') {
                $actionWord = 'deleted';
            }
            return "{$role} {$actionWord} Service SRA {$sraNum} (the document confirming that new items were received into the store).";
        }

        return null;
    }

    private function getDetailedAdminAction($request, $role, $path, $method)
    {
        if (in_array($method, ['GET', 'HEAD'])) {
            return null;
        }

        // 1. Settings update
        if ($path === 'admin/settings') {
            if ($request->has('stores_dept_head_approval_categories_present')) {
                $cats = $request->input('stores_dept_head_approval_categories', []);
                if (empty($cats)) {
                    return "{$role} updated the Stores Department Head approval workflow categories to bypass all categories.";
                }
                return "{$role} updated the Stores Department Head approval workflow categories to: " . implode(', ', $cats) . ".";
            }

            if ($request->has('dg_approval_categories_present')) {
                $cats = $request->input('dg_approval_categories', []);
                if (empty($cats)) {
                    return "{$role} updated the Director General approval workflow categories to bypass all categories.";
                }
                return "{$role} updated the Director General approval workflow categories to: " . implode(', ', $cats) . ".";
            }

            // General settings form
            $inputs = $request->except(['_token', 'settings_form']);
            if (!empty($inputs)) {
                $keys = array_keys($inputs);
                $readableKeys = array_map(function($k) {
                    return ucwords(str_replace('_', ' ', $k));
                }, $keys);
                return "{$role} updated the following system settings: " . implode(', ', $readableKeys) . ".";
            }

            return "{$role} updated system settings.";
        }

        // 2. Settings sub-endpoints
        if ($path === 'admin/settings/category') {
            return "{$role} added a new item category: {$request->input('code')} ({$request->input('name')}).";
        }

        if (preg_match('#^admin/settings/category/([^/]+)/update$#', $path, $matches)) {
            $code = $matches[1];
            return "{$role} updated item category {$code} name to: {$request->input('name')}.";
        }

        if ($path === 'admin/settings/unit-rule') {
            return "{$role} updated unit rules configuration.";
        }

        if ($path === 'admin/settings/threshold-rule') {
            return "{$role} updated low stock threshold rules configuration.";
        }

        if ($path === 'admin/settings/request-limit') {
            return "{$role} updated request limit rules configuration.";
        }

        if ($path === 'admin/settings/supplier-registry') {
            return "{$role} updated the supplier registry.";
        }

        // 3. User permissions & toggles
        if ($path === 'admin/permissions/update') {
            $tgtUser = \App\Models\User::find($request->input('user_id'));
            $tgtName = $tgtUser ? $tgtUser->name : "User ID " . $request->input('user_id');
            return "{$role} updated access permissions for {$tgtName}.";
        }

        if ($path === 'admin/permissions/toggle-department') {
            $tgtUser = \App\Models\User::find($request->input('user_id'));
            $tgtName = $tgtUser ? $tgtUser->name : "User ID " . $request->input('user_id');
            return "{$role} updated department access matrix for {$tgtName} (department: {$request->input('department')}).";
        }

        // 4. User CRUD and Approvals
        if ($path === 'admin/users' && $method === 'POST') {
            return "{$role} registered a new user account: {$request->input('name')} (@{$request->input('username')}) with role: {$request->input('role')}.";
        }

        if (preg_match('#^admin/users/(\d+)/approve-registration$#', $path, $matches)) {
            $tgtUser = \App\Models\User::find($matches[1]);
            $tgtName = $tgtUser ? $tgtUser->name : "User ID " . $matches[1];
            return "{$role} approved registration request for {$tgtName}.";
        }

        if (preg_match('#^admin/users/(\d+)/reject-registration$#', $path, $matches)) {
            $tgtUser = \App\Models\User::find($matches[1]);
            $tgtName = $tgtUser ? $tgtUser->name : "User ID " . $matches[1];
            return "{$role} rejected registration request for {$tgtName}.";
        }

        if (preg_match('#^admin/users/(\d+)/toggle-status$#', $path, $matches)) {
            $tgtUser = \App\Models\User::find($matches[1]);
            $tgtName = $tgtUser ? $tgtUser->name : "User ID " . $matches[1];
            $statusWord = ($tgtUser && $tgtUser->is_active) ? 'suspended' : 'activated';
            return "{$role} {$statusWord} the user account for {$tgtName}.";
        }

        if (preg_match('#^admin/users/(\d+)$#', $path, $matches)) {
            $tgtUser = \App\Models\User::find($matches[1]);
            $tgtName = $tgtUser ? $tgtUser->name : "User ID " . $matches[1];
            if ($method === 'DELETE') {
                return "{$role} deleted the user account for {$tgtName}.";
            }
            return "{$role} modified profile details for {$tgtName}.";
        }

        // 5. Password requests
        if (preg_match('#^admin/password-requests/(\d+)/approve$#', $path, $matches)) {
            $req = \App\Models\PasswordResetRequest::find($matches[1]);
            $username = $req ? $req->username : "ID " . $matches[1];
            return "{$role} approved password reset request for user: {$username}.";
        }

        if (preg_match('#^admin/password-requests/(\d+)/reject$#', $path, $matches)) {
            $req = \App\Models\PasswordResetRequest::find($matches[1]);
            $username = $req ? $req->username : "ID " . $matches[1];
            return "{$role} rejected password reset request for user: {$username}.";
        }

        // 6. Requisition processing
        if (preg_match('#^admin/requisitions/(\d+)/process$#', $path, $matches)) {
            $req = \App\Models\StoreRequisition::find($matches[1]);
            $reqRef = $req ? $req->requisition_number : "requisition #{$matches[1]}";
            $statusWord = $request->input('status') === 'approved' ? 'approved' : 'declined';
            return "{$role} {$statusWord} store {$reqRef}.";
        }

        // 7. SRA processing
        if (preg_match('#^admin/service-sra/(\d+)/process$#', $path, $matches)) {
            $sra = \App\Models\ServiceSra::find($matches[1]);
            $sraNum = $sra ? $sra->sra_number : "SRA #{$matches[1]}";
            $statusWord = $request->input('status') === 'approved' ? 'approved' : 'declined';
            return "{$role} {$statusWord} Service SRA {$sraNum}.";
        }

        return null;
    }
}
