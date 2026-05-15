<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$val = App\Models\Setting::get('max_login_attempts', 'NOT FOUND');
echo "Value: " . $val . "\n";
echo "Type: " . gettype($val) . "\n";
