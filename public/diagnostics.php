<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "<h1>System Diagnostics</h1>";

// 1. Check Table Structure
$columns = DB::getSchemaBuilder()->getColumnListing('users');
echo "<h3>Users Table Columns:</h3><pre>";
print_r($columns);
echo "</pre>";

// 2. Check Admin Users
$admins = User::where('is_admin', true)->get();
echo "<h3>Admin Users in DB:</h3><table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Username</th><th>Is Admin (Raw)</th><th>Is Online (Raw)</th><th>Last Login</th></tr>";
foreach ($admins as $admin) {
    echo "<tr>";
    echo "<td>" . $admin->id . "</td>";
    echo "<td>" . $admin->username . "</td>";
    echo "<td>" . var_export($admin->getRawOriginal('is_admin'), true) . "</td>";
    echo "<td>" . var_export($admin->getRawOriginal('is_online'), true) . "</td>";
    echo "<td>" . $admin->last_login_at . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Check Session Info
echo "<h3>Current Session Info:</h3>";
echo "Authenticated: " . (auth()->check() ? "YES" : "NO") . "<br>";
if (auth()->check()) {
    echo "Logged in as ID: " . auth()->id() . "<br>";
    echo "Role: " . auth()->user()->role . "<br>";
}
?>
