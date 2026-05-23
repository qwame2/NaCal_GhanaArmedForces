<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$rows = \DB::select("
    SELECT
        sri.id,
        sri.description,
        sri.quantity           AS requested,
        sri.quantity_approved  AS approved,
        sri.alternative_description,
        sri.alternative_quantity_approved,
        sr.status
    FROM store_requisition_items sri
    JOIN store_requisitions sr ON sri.requisition_id = sr.id
    WHERE LOWER(sri.description) LIKE '%pen%'
      AND sr.status IN ('approved','partially_approved')
    ORDER BY sri.id
");

$total = 0;
echo "<style>body{font-family:monospace;font-size:13px;} table{border-collapse:collapse;} td,th{border:1px solid #ccc;padding:6px 12px;}</style>";
echo "<h2>PEN items in approved/partially_approved requisitions</h2>";
echo "<table>";
echo "<tr><th>ID</th><th>Description</th><th>Requested</th><th>Approved</th><th>Alt Description</th><th>Alt Approved</th><th>Status</th></tr>";
foreach ($rows as $r) {
    $total += (float)$r->approved;
    $flag = ((float)$r->approved < (float)$r->requested) ? ' style="background:#fee2e2"' : '';
    echo "<tr{$flag}>";
    echo "<td>{$r->id}</td>";
    echo "<td>{$r->description}</td>";
    echo "<td>{$r->requested}</td>";
    echo "<td><strong>{$r->approved}</strong></td>";
    echo "<td>" . ($r->alternative_description ?? '-') . "</td>";
    echo "<td>" . ($r->alternative_quantity_approved ?? '-') . "</td>";
    echo "<td>{$r->status}</td>";
    echo "</tr>";
}
echo "</table>";
echo "<br><strong>TOTAL quantity_approved for PEN items: {$total}</strong>";
echo "<br><em>(Rows highlighted in red have approved &lt; requested)</em>";
