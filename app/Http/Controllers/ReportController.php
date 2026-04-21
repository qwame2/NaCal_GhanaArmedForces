<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryBatch;
use App\Models\IssuedItem;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'monthly');
        
        $startDate = Carbon::now();
        $endDate = Carbon::now();
        $dateLabel = "General Report";

        if ($period === 'daily') {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $dateLabel = "Daily Activity Report - " . $startDate->format('F j, Y');
        } elseif ($period === 'monthly') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $dateLabel = "Monthly Overview Report - " . $startDate->format('F Y');
        } elseif ($period === 'yearly') {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
            $dateLabel = "Annual Summary Report - " . $startDate->format('Y');
        }

        // Received Metrics
        $receivedQuery = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->whereBetween('inventory_batches.entry_date', [$startDate, $endDate]);

        $totalReceivedBatches = $receivedQuery->count();
        $totalReceivedQty = $receivedQuery->sum('qty');

        // Issued Metrics
        $issuedQuery = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->whereBetween('issuances.issuance_date', [$startDate, $endDate]);

        $totalIssuedBatches = $issuedQuery->count();
        $totalIssuedQty = $issuedQuery->sum('quantity');
        
        // Let's get combined recent activity list
        $recentReceivals = InventoryItem::join('inventory_batches', 'inventory_items.batch_id', '=', 'inventory_batches.id')
            ->whereBetween('inventory_batches.entry_date', [$startDate, $endDate])
            ->select('inventory_items.*', 'inventory_batches.entry_date', 'inventory_batches.supplier_name', 'inventory_batches.ledge_category', \DB::raw("'Received' as transaction_type"))
            ->orderBy('inventory_batches.entry_date', 'desc')
            ->limit(50)
            ->get();
            
        $recentIssues = IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->whereBetween('issuances.issuance_date', [$startDate, $endDate])
            ->select('issued_items.*', 'issuances.issuance_date as entry_date', 'issuances.beneficiary', \DB::raw("'Issued' as transaction_type"))
            ->orderBy('issuances.issuance_date', 'desc')
            ->limit(50)
            ->get();
            
        // Sorting them together requires mapping or just passing them to view
        // Let's pass them to view and the view can render two tabs or tables

        return view('reports.index', compact(
            'period', 
            'dateLabel', 
            'totalReceivedBatches', 
            'totalReceivedQty',
            'totalIssuedBatches',
            'totalIssuedQty',
            'recentReceivals',
            'recentIssues',
            'startDate',
            'endDate'
        ));
    }
}
