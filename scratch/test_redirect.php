<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "--- Redirect Logic Verification ---\n\n";

$roles = [
    ['role' => 'Main Admin', 'is_admin' => true, 'expected' => 'main-admin.requisitions'],
    ['role' => 'Department Head', 'is_admin' => false, 'expected' => 'main-admin.requisitions'],
    ['role' => 'Auditor', 'is_admin' => false, 'expected' => 'auditor.dashboard'],
    ['role' => 'Requisitioner', 'is_admin' => false, 'expected' => 'requisitions.index'],
    ['role' => 'Admin', 'is_admin' => true, 'expected' => 'admin.index'],
    ['role' => 'Officer', 'is_admin' => false, 'expected' => 'dashboard'],
];

foreach ($roles as $r) {
    $user = new User([
        'role' => $r['role'],
        'is_admin' => $r['is_admin'],
    ]);

    // 1. Emulate showAuth() redirect logic:
    $showAuthRedirect = '';
    if (in_array($user->role, ['Main Admin', 'Department Head'])) {
        $showAuthRedirect = 'main-admin.requisitions';
    } elseif ($user->role === 'Auditor') {
        $showAuthRedirect = 'auditor.dashboard';
    } elseif ($user->is_admin) {
        $showAuthRedirect = 'admin.index';
    } else {
        $showAuthRedirect = 'dashboard';
    }

    // 2. Emulate default fallback redirect logic in login():
    $loginFallbackRedirect = '';
    if (in_array($user->role, ['Main Admin', 'Department Head'])) {
        $loginFallbackRedirect = 'main-admin.requisitions';
    } elseif ($user->role === 'Auditor') {
        $loginFallbackRedirect = 'auditor.dashboard';
    } elseif ($user->role === 'Requisitioner') {
        $loginFallbackRedirect = 'requisitions.index';
    } elseif ($user->is_admin) {
        $loginFallbackRedirect = 'admin.index';
    } else {
        $loginFallbackRedirect = 'dashboard';
    }

    // 3. Emulate Scenario 1 (admin terminal target):
    $adminTerminalRedirect = '';
    $target = 'admin';
    if (in_array($user->role, ['Main Admin', 'Department Head'])) {
        $adminTerminalRedirect = 'main-admin.requisitions';
    } elseif ($user->role === 'Auditor') {
        $adminTerminalRedirect = 'auditor.dashboard';
    } elseif ($user->is_admin) {
        $adminTerminalRedirect = 'admin.index';
    } else {
        $adminTerminalRedirect = 'LOGOUT_VIOLATION';
    }

    // 4. Emulate Scenario 2 (personnel terminal target):
    $userTerminalRedirect = '';
    $target = 'user';
    if (in_array($user->role, ['Main Admin', 'Department Head'])) {
        $userTerminalRedirect = 'main-admin.requisitions';
    } elseif ($user->is_admin) {
        $userTerminalRedirect = 'admin.index';
    } elseif ($user->role === 'Auditor') {
        $userTerminalRedirect = 'auditor.dashboard';
    } elseif ($user->role === 'Requisitioner') {
        $userTerminalRedirect = 'requisitions.index';
    } else {
        $userTerminalRedirect = 'dashboard';
    }

    // 5. Emulate routes/web.php dashboard route:
    $dashboardRedirect = '';
    if ($user->role === 'Main Admin') {
        $dashboardRedirect = 'main-admin.requisitions';
    } elseif ($user->is_admin) {
        $dashboardRedirect = 'admin.index';
    } elseif ($user->role === 'Auditor') {
        $dashboardRedirect = 'auditor.dashboard';
    } elseif ($user->role === 'Requisitioner') {
        $dashboardRedirect = 'requisitions.index';
    } else {
        $dashboardRedirect = 'dashboard_view';
    }

    echo "Role: " . str_pad($user->role, 18) . " | is_admin: " . ($user->is_admin ? 'Yes' : 'No ') . "\n";
    echo "  - showAuth() redirect:       " . str_pad($showAuthRedirect, 25) . " (Expected: {$r['expected']})\n";
    echo "  - login() fallback:          " . str_pad($loginFallbackRedirect, 25) . " (Expected: {$r['expected']})\n";
    echo "  - admin terminal redirect:   " . str_pad($adminTerminalRedirect, 25) . " (Expected: " . ($r['role'] === 'Officer' ? 'LOGOUT_VIOLATION' : $r['expected']) . ")\n";
    echo "  - user terminal redirect:    " . str_pad($userTerminalRedirect, 25) . " (Expected: {$r['expected']})\n";
    echo "  - dashboard route redirect:  " . str_pad($dashboardRedirect, 25) . " (Expected: " . ($r['role'] === 'Officer' ? 'dashboard_view' : ($r['role'] === 'Department Head' ? 'dashboard_view' : $r['expected'])) . ")\n";
    echo "\n";
}

echo "Verification Finished!\n";
