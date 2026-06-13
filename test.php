<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::all();
echo "Total Users found: " . $users->count() . "\n\n";
foreach ($users as $u) {
    echo "ID: " . $u->id . "\n";
    echo "Name: " . $u->name . "\n";
    echo "Username: " . $u->username . "\n";
    echo "Role: " . $u->role . "\n";
    echo "Is Active: " . ($u->is_active ? 'YES' : 'NO') . "\n";
    echo "Reg Status: " . $u->registration_status . "\n";
    echo "-----------------------------------------\n\n";
}


