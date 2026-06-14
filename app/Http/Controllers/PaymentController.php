<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['invoice', 'invoice.customer'])->orderBy('created_at', 'desc')->get();
        $invoices = Invoice::where('status', '!=', 'paid')->with('customer')->get();
        $setting = \App\Models\CompanySetting::first() ?? new \App\Models\CompanySetting(['currency' => '$']);
        return view('admin.payments', compact('payments', 'invoices', 'setting'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        Payment::create($validated);

        $invoice = Invoice::findOrFail($validated['invoice_id']);
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } else {
            $invoice->update(['status' => 'partial']);
        }

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();

        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid == 0) {
            $invoice->update(['status' => 'pending']);
        } elseif ($totalPaid >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } else {
            $invoice->update(['status' => 'partial']);
        }

        return redirect()->route('payments.index')->with('success', 'Payment deleted successfully.');
    }
}
