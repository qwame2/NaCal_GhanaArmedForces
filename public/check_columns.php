<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = Schema::getColumnListing('edit_requests');
echo "Columns in edit_requests table:<br><pre>";
print_r($columns);
echo "</pre>";

$hasColumn = Schema::hasColumn('edit_requests', 'request_type');
echo "Has 'request_type': " . ($hasColumn ? 'YES' : 'NO');
