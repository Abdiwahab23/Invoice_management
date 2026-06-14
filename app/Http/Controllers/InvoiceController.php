<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('customer')->orderBy('created_at', 'desc')->get();
        $setting = CompanySetting::first() ?? new CompanySetting(['currency' => '$']);
        return view('admin.invoices.index', compact('invoices', 'setting'));
    }

    public function create()
    {
        $customers = Customer::orderBy('customer_name')->get();
        $setting = CompanySetting::first() ?? new CompanySetting(['default_tax_rate' => 0, 'tax_name' => 'Tax', 'currency' => '$']);
        return view('admin.invoices.create', compact('customers', 'setting'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'tax_rate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {
            // Generate Invoice Number (e.g., INV-00001)
            $lastInvoice = Invoice::orderBy('id', 'desc')->first();
            $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;
            $invoiceNumber = 'INV-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            // Calculate Totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += ($item['quantity'] * $item['price']);
            }
            $taxAmount = $subtotal * ($request->tax_rate / 100);
            $totalAmount = $subtotal + $taxAmount;

            $invoice = Invoice::create([
                'customer_id' => $request->customer_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'total_amount' => $totalAmount,
                'tax_rate' => $request->tax_rate,
                'status' => 'pending',
                'notes' => $request->notes
            ]);

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['price'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $lineTotal
                ]);
            }
        });

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items', 'payments']);
        $setting = CompanySetting::first() ?? new CompanySetting(['company_name' => 'InvoicePro', 'currency' => '$']);
        return view('admin.invoices.show', compact('invoice', 'setting'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['items']);
        $customers = Customer::orderBy('customer_name')->get();
        $setting = CompanySetting::first() ?? new CompanySetting(['default_tax_rate' => 0, 'tax_name' => 'Tax', 'currency' => '$']);
        return view('admin.invoices.edit', compact('invoice', 'customers', 'setting'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'tax_rate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request, $invoice) {
            // Calculate Totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += ($item['quantity'] * $item['price']);
            }
            $taxAmount = $subtotal * ($request->tax_rate / 100);
            $totalAmount = $subtotal + $taxAmount;

            $invoice->update([
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'total_amount' => $totalAmount,
                'tax_rate' => $request->tax_rate,
                'notes' => $request->notes
            ]);

            // Delete old items and insert new ones
            $invoice->items()->delete();

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['price'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $lineTotal
                ]);
            }

            // Recalculate status based on payments
            $totalPaid = $invoice->payments()->sum('amount');
            if ($totalPaid == 0) {
                $invoice->update(['status' => 'pending']);
            } elseif ($totalPaid >= $invoice->total_amount) {
                $invoice->update(['status' => 'paid']);
            } else {
                $invoice->update(['status' => 'partial']);
            }
        });

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }
}
