<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'total_invoices' => Invoice::count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'monthly_revenue' => Payment::whereMonth('payment_date', Carbon::now()->month)
                                        ->whereYear('payment_date', Carbon::now()->year)
                                        ->sum('amount'),
            'outstanding_balance' => Invoice::where('status', '!=', 'paid')->sum('total_amount') - Payment::whereHas('invoice', function($q) {
                $q->where('status', '!=', 'paid');
            })->sum('amount'),
            'overdue_invoices' => Invoice::where('status', 'overdue')->orWhere(function($q) {
                $q->where('status', '!=', 'paid')->whereDate('due_date', '<', Carbon::now());
            })->count(),
            'partial_invoices' => Invoice::where('status', 'partial')->count(),
        ];

        // Monthly Revenue for Chart (Last 6 months)
        $chartMonths = [];
        $chartRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $chartMonths[] = $date->format('M Y');
            $chartRevenue[] = Payment::whereMonth('payment_date', $date->month)
                                     ->whereYear('payment_date', $date->year)
                                     ->sum('amount');
        }
        $chartData = [
            'months' => $chartMonths,
            'revenue' => $chartRevenue
        ];

        $recentInvoices = Invoice::with('customer')->orderBy('created_at', 'desc')->take(5)->get();
        $recentPayments = Payment::with(['invoice', 'invoice.customer'])->orderBy('created_at', 'desc')->take(5)->get();
        $setting = \App\Models\CompanySetting::first() ?? new \App\Models\CompanySetting(['currency' => '$']);

        return view('admin.dashboard', compact('stats', 'recentInvoices', 'recentPayments', 'chartData', 'setting'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        if (!$query || strlen($query) < 2) {
            return response()->json(['customers' => [], 'invoices' => []]);
        }

        $customers = Customer::where('customer_name', 'like', "%{$query}%")
                             ->orWhere('email', 'like', "%{$query}%")
                             ->orWhere('phone', 'like', "%{$query}%")
                             ->orWhere('company_name', 'like', "%{$query}%")
                             ->take(5)
                             ->get(['id', 'customer_name', 'email']);

        $invoices = Invoice::with('customer')
                           ->where('invoice_number', 'like', "%{$query}%")
                           ->orWhereHas('customer', function($q) use ($query) {
                               $q->where('customer_name', 'like', "%{$query}%");
                           })
                           ->take(5)
                           ->get(['id', 'invoice_number', 'total_amount', 'status', 'customer_id']);

        return response()->json([
            'customers' => $customers,
            'invoices' => $invoices
        ]);
    }
}
