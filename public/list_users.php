<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all();
foreach ($users as $user) {
    echo "ID: {$user->id} | Name: {$user->name} | Role: {$user->role} | Dept: {$user->department} | Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
}
