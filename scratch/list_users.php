<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = App\Models\User::all()->map(function($u) {
    return [
        'id' => $u->id,
        'username' => $u->username,
        'role' => $u->role,
        'is_admin' => $u->is_admin,
        'is_temp_account' => $u->is_temp_account,
        'is_active' => $u->is_active
    ];
})->toArray();

echo json_encode($users, JSON_PRETTY_PRINT) . PHP_EOL;
