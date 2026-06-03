<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$admins = User::where('is_admin', true)->get();
echo "Total Admins found: " . $admins->count() . "\n\n";
foreach ($admins as $u) {
    echo "ID: " . $u->id . "\n";
    echo "Name: " . $u->name . "\n";
    echo "Username: " . $u->username . "\n";
    echo "Role: " . $u->role . "\n";
    echo "Password Hash: " . $u->password . "\n";
    
    // Check if the password is "password" or "admin123" or something else
    $testPasswords = ['password', 'Password123', 'admin123', 'admin', '12345678', 'Password123!'];
    $foundPass = 'None';
    foreach ($testPasswords as $tp) {
        if (\Hash::check($tp, $u->password)) {
            $foundPass = $tp;
            break;
        }
    }
    echo "Matches test password: " . $foundPass . "\n";
    echo "Needs rehash: " . (\Hash::needsRehash($u->password) ? 'YES' : 'NO') . "\n";
    echo "-----------------------------------------\n\n";
}

