<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // 1. Revenue by Month (Current Year)
        $monthlyRevenue = Payment::select(
            DB::raw('MONTH(payment_date) as month'),
            DB::raw('SUM(amount) as total')
        )
        ->whereYear('payment_date', Carbon::now()->year)
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->keyBy('month')
        ->toArray();

        $revenueData = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenueData[] = isset($monthlyRevenue[$i]) ? $monthlyRevenue[$i]['total'] : 0;
        }

        // 2. Invoice Status Summary
        $statusSummary = Invoice::select('status', DB::raw('count(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('status')
            ->get();

        // 3. Top Customers by Revenue
        $topCustomers = Customer::withSum(['invoices' => function($query) {
            $query->where('status', 'paid');
        }], 'total_amount')
        ->orderByDesc('invoices_sum_total_amount')
        ->take(5)
        ->get();

        // 4. Recent Payments
        $recentPayments = Payment::with(['invoice', 'invoice.customer'])->orderByDesc('created_at')->take(10)->get();

        // 5. Global Stats
        $totalRevenue = Payment::sum('amount');
        $totalPaidInvoices = Invoice::where('status', 'paid')->count();
        $totalUnpaidInvoices = Invoice::where('status', '!=', 'paid')->count();
        
        // 6. Last 5 Invoices
        $last5Invoices = Invoice::with('customer')->orderByDesc('created_at')->take(5)->get();

        $setting = \App\Models\CompanySetting::first() ?? new \App\Models\CompanySetting(['currency' => '$']);

        return view('admin.reports', compact(
            'revenueData', 'statusSummary', 'topCustomers', 'recentPayments', 
            'totalRevenue', 'totalPaidInvoices', 'totalUnpaidInvoices', 'last5Invoices', 'setting'
        ));
    }
}
