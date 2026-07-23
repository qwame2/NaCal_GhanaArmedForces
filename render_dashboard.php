<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
$user = User::where('username', 'atto')->first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
}

try {
    // Let's trigger the HTTP request programmatically:
    $request = \Illuminate\Http\Request::create('/dashboard', 'GET');
    $response = app()->handle($request);
    echo "STATUS: " . $response->getStatusCode() . "\n";
    file_put_contents('rendered_dashboard.html', $response->getContent());
    echo "RENDERED SUCCESSFULLY\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
