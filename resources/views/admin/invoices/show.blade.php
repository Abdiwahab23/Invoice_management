@extends('layouts.admin')

@section('title', 'View Invoice - InvoicePro')

@section('content')
<div class="row mb-4 no-print">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">Invoice {{ $invoice->invoice_number }}</h2>
            <a href="{{ route('invoices.index') }}" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Back to Invoices</a>
        </div>
        <div>
            <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-primary me-2"><i class="fas fa-edit me-1"></i> Edit Invoice</a>
            <button class="btn btn-outline-secondary me-2" onclick="window.print()"><i class="fas fa-print me-1"></i> Print / PDF</button>
            @if($invoice->status !== 'paid')
                <a href="{{ route('payments.index') }}" class="btn btn-success"><i class="fas fa-money-bill-wave me-1"></i> Add Payment</a>
            @endif
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show no-print">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="invoice-container bg-white shadow-sm print-container">
    <div class="invoice-wrapper">
        
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-left">
                <div class="logo-container">
                    @if(isset($setting->logo) && $setting->logo)
                        <img src="{{ asset('storage/' . $setting->logo) }}" alt="Company Logo" class="invoice-logo">
                    @endif
                    <h2 class="company-name-fallback">{{ $setting->company_name ?? 'InvoicePro' }}</h2>
                </div>
                
                <div class="company-details">
                    @if($setting->address) <div class="detail-line">{!! nl2br(e($setting->address)) !!}</div> @endif
                    @if($setting->email) <div class="detail-line"><i class="fas fa-envelope icon"></i> {{ $setting->email }}</div> @endif
                    @if($setting->phone) <div class="detail-line"><i class="fas fa-phone icon"></i> {{ $setting->phone }}</div> @endif
                    <div class="detail-line mt-1"><strong>Tax ID:</strong> {{ $setting->tax_name ?? 'TAX-123456' }}</div>
                </div>
            </div>
            
            <div class="header-right">
                <h1 class="invoice-title">INVOICE</h1>
                
                <table class="invoice-meta-table">
                    <tr>
                        <td class="meta-label">Invoice No:</td>
                        <td class="meta-value"><strong>{{ $invoice->invoice_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="meta-label">Date:</td>
                        <td class="meta-value">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Due Date:</td>
                        <td class="meta-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Status:</td>
                        <td class="meta-value"><strong>{{ strtoupper($invoice->status) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Bill To -->
        <div class="billed-to-section">
            <div class="billed-to-label">BILLED TO:</div>
            <div class="customer-name">{{ $invoice->customer->customer_name }}</div>
            <div class="customer-details">
                @if($invoice->customer->company_name) <div class="cust-line">{{ $invoice->customer->company_name }}</div> @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="invoice-items-wrapper">
            <table class="invoice-items-table">
                <thead>
                    <tr>
                        <th class="col-desc">Item Description</th>
                        <th class="col-qty">Qty</th>
                        <th class="col-price">Price</th>
                        <th class="col-total">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subtotal = 0; @endphp
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="col-desc">
                            <span class="item-name">{{ $item->item_description }}</span>
                        </td>
                        <td class="col-qty">{{ $item->quantity }}</td>
                        <td class="col-price">{{ $setting->currency }}{{ number_format($item->price, 2) }}</td>
                        <td class="col-total"><strong>{{ $setting->currency }}{{ number_format($item->total, 2) }}</strong></td>
                    </tr>
                    @php $subtotal += $item->total; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="invoice-footer">
            <div class="notes-section">
                <!-- Notes removed as per user request -->
            </div>
            
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Subtotal:</td>
                        <td class="total-value">{{ $setting->currency }}{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->tax_rate > 0)
                    <tr>
                        <td class="total-label">{{ $setting->tax_name ?? 'Tax' }} ({{ $invoice->tax_rate }}%):</td>
                        @php $taxAmount = $subtotal * ($invoice->tax_rate / 100); @endphp
                        <td class="total-value">{{ $setting->currency }}{{ number_format($taxAmount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->payments()->sum('amount') > 0)
                    <tr>
                        <td class="total-label">Amount Paid:</td>
                        <td class="total-value">-{{ $setting->currency }}{{ number_format($invoice->payments()->sum('amount'), 2) }}</td>
                    </tr>
                    @endif
                </table>
                <div class="totals-divider"></div>
                <table class="totals-table">
                    <tr>
                        <td class="total-label final-total-label">Total:</td>
                        @php $balance = $invoice->total_amount - $invoice->payments()->sum('amount'); @endphp
                        <td class="total-value final-total-value">{{ $setting->currency }}{{ number_format($balance, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="invoice-bottom-footer">
            Thank you for your business. Please make payments by the due date.
        </div>

    </div>
</div>

<style>
    /* Reset & Base */
    .invoice-container {
        max-width: 800px;
        margin: 0 auto;
        font-family: 'Inter', 'Helvetica Neue', Helvetica, sans-serif;
        color: #475569;
        background: white;
    }
    .invoice-wrapper {
        padding: 40px;
    }
    
    /* Bulletproof Table Layout */
    .invoice-header, .invoice-footer {
        display: table;
        width: 100%;
        margin-bottom: 25px;
        table-layout: fixed;
    }
    .header-left {
        display: table-cell;
        width: 55%;
        vertical-align: top;
        padding-right: 15px;
    }
    .header-right {
        display: table-cell;
        width: 45%;
        vertical-align: top;
        text-align: right;
    }
    .notes-section {
        display: table-cell;
        width: 55%;
        vertical-align: top;
        padding-right: 25px;
    }
    .totals-section {
        display: table-cell;
        width: 45%;
        vertical-align: top;
    }
    
    /* Header Elements */
    .logo-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .invoice-logo {
        height: 55px; /* Smaller logo */
        object-fit: contain;
        max-width: 100%;
        margin-right: 15px;
    }
    .company-name-fallback {
        font-weight: 800;
        color: #1e293b;
        font-size: 22px;
        margin: 0;
    }
    .company-details {
        font-size: 12px; /* Smaller font */
        line-height: 1.6;
        color: #64748b;
    }
    .company-details .icon {
        width: 14px;
        text-align: center;
        margin-right: 6px;
        color: #94a3b8;
    }
    .company-details strong {
        color: #475569;
    }
    .mt-1 { margin-top: 4px; }
    
    .invoice-title {
        color: #64748b;
        font-size: 32px; /* Smaller title */
        font-weight: 800;
        letter-spacing: 2px;
        margin-bottom: 15px;
        text-transform: uppercase;
        margin-top: 0;
    }
    .invoice-meta-table {
        width: auto;
        margin-left: auto;
        font-size: 12px; /* Smaller meta */
        border-collapse: collapse;
    }
    .invoice-meta-table td {
        padding: 3px 0;
        white-space: nowrap;
    }
    .meta-label {
        color: #64748b;
        padding-right: 12px !important;
        text-align: right;
    }
    .meta-value {
        color: #1e293b;
        text-align: right;
        min-width: 90px;
    }
    
    /* Billed To */
    .billed-to-section {
        margin-bottom: 25px;
    }
    .billed-to-label {
        color: #64748b;
        font-weight: 700;
        font-size: 11px; /* Smaller label */
        margin-bottom: 6px;
        letter-spacing: 0.5px;
    }
    .customer-name {
        font-weight: 700;
        font-size: 15px; /* Smaller name */
        color: #1e293b;
        margin-bottom: 4px;
    }
    .customer-details {
        font-size: 12px;
        color: #64748b;
        line-height: 1.6;
    }

    /* Items Table */
    .invoice-items-wrapper {
        margin-bottom: 25px;
    }
    .invoice-items-table {
        width: 100%;
        border-collapse: collapse;
    }
    .invoice-items-table th {
        background-color: #1e293b !important;
        color: #ffffff !important;
        font-weight: 600;
        font-size: 12px; /* Smaller TH */
        padding: 10px 12px;
        text-align: left;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .invoice-items-table td {
        padding: 10px 12px; /* Tighter cells */
        border-bottom: 1px solid #e2e8f0;
        color: #475569;
        font-size: 12px;
        vertical-align: top;
    }
    /* Vertical borders */
    .invoice-items-table th.col-qty, .invoice-items-table td.col-qty,
    .invoice-items-table th.col-price, .invoice-items-table td.col-price,
    .invoice-items-table th.col-total, .invoice-items-table td.col-total {
        border-left: 1px solid #e2e8f0;
    }
    .invoice-items-table th.col-qty,
    .invoice-items-table th.col-price,
    .invoice-items-table th.col-total {
        border-left: 1px solid #334155;
    }
    
    .col-desc { text-align: left; width: 45%; }
    .col-qty { text-align: center; width: 10%; }
    .col-price { text-align: right; width: 20%; }
    .col-total { text-align: right; width: 25%; }
    
    .item-name {
        font-weight: 700;
        color: #1e293b;
        display: block;
    }

    /* Footer Details */
    .notes-label {
        color: #64748b;
        font-weight: 700;
        font-size: 11px;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }
    .notes-box {
        background-color: #f8fafc !important;
        padding: 12px;
        border-radius: 4px;
        font-size: 12px; /* Smaller text */
        color: #64748b;
        line-height: 1.5;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .totals-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px; /* Smaller totals */
        margin-left: auto;
    }
    .totals-table td {
        padding: 5px 0;
    }
    .total-label {
        text-align: right;
        color: #64748b;
        padding-right: 15px;
    }
    .total-value {
        text-align: right;
        color: #1e293b;
        font-weight: 600;
        min-width: 90px;
    }
    .totals-divider {
        border-top: 1px solid #e2e8f0;
        margin: 8px 0;
    }
    .final-total-label {
        font-size: 15px;
        font-weight: 800;
        color: #1e293b;
    }
    .final-total-value {
        font-size: 15px;
        font-weight: 800;
        color: #0ea5e9 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .invoice-bottom-footer {
        text-align: center;
        border-top: 1px solid #e2e8f0;
        padding-top: 15px;
        font-size: 11px;
        color: #94a3b8;
    }

    /* Print Overrides */
    @media print {
        @page { size: A4; margin: 0; }
        body { 
            background-color: #fff; 
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important; 
        }
        #sidebar-wrapper, .navbar, .no-print { display: none !important; }
        #page-content-wrapper { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
        
        .invoice-container { 
            box-shadow: none !important; 
            border: none !important; 
            max-width: 100% !important; 
            margin: 0 !important;
            padding: 15mm 15mm 0 15mm !important; /* Proper A4 margins */
        }
        
        /* Eliminate internal margins to guarantee 1 page */
        .invoice-wrapper { padding: 0 !important; }
        .invoice-header { margin-bottom: 20px !important; }
        .logo-container { margin-bottom: 15px !important; }
        .invoice-title { margin-bottom: 10px !important; }
        .billed-to-section { margin-bottom: 15px !important; }
        .invoice-items-wrapper { margin-bottom: 15px !important; }
        
        .invoice-title { color: #64748b !important; }
        .final-total-value { color: #0ea5e9 !important; }
        .invoice-items-table th { background-color: #1e293b !important; color: #ffffff !important; }
        .notes-box { background-color: #f8fafc !important; }
    }
</style>

@if(request('action') === 'download')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    window.addEventListener('load', function() {
        // Temporarily hide the "no-print" elements for the screenshot
        document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
        
        var element = document.querySelector('.invoice-container');
        
        // Options for the PDF generation
        var opt = {
            margin:       10,
            filename:     'Invoice_{{ $invoice->invoice_number }}.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        // Generate and instantly download
        html2pdf().set(opt).from(element).save().then(function() {
            // Bring the buttons back after download completes
            document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
            // Optionally, redirect back to the invoices list
            window.location.href = "{{ route('invoices.index') }}";
        });
    });
</script>
@endif

@endsection
