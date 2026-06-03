<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$admins = User::where('is_admin', true)->get();
echo "Found Admins:\n";
foreach ($admins as $admin) {
    echo "ID: {$admin->id} | Name: {$admin->name} | Username: {$admin->username} | Role: {$admin->role} | Active: {$admin->is_active}\n";
}
