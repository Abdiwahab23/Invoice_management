@extends('layouts.admin')

@section('title', 'Edit Invoice - InvoicePro')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">Edit Invoice {{ $invoice->invoice_number }}</h2>
            <a href="{{ route('invoices.show', $invoice->id) }}" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Back to Invoice</a>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('invoices.update', $invoice->id) }}">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 border-bottom pb-2">Invoice Details</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ $c->id == $invoice->customer_id ? 'selected' : '' }}>{{ $c->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="invoice_date" value="{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="due_date" value="{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 border-bottom pb-2 mt-5">Invoice Items</h5>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Description</th>
                                    <th width="120">Quantity</th>
                                    <th width="150">Price ({{ $setting->currency }})</th>
                                    <th width="150">Total</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="invoiceItems">
                                @foreach($invoice->items as $index => $item)
                                <tr>
                                    <td><input type="text" class="form-control" name="items[{{ $index }}][description]" value="{{ $item->item_description }}" required></td>
                                    <td><input type="number" class="form-control qty-input" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1" required></td>
                                    <td><input type="number" step="0.01" class="form-control price-input" name="items[{{ $index }}][price]" value="{{ $item->price }}" min="0" required></td>
                                    <td><input type="text" class="form-control item-total" readonly value="{{ number_format($item->total, 2, '.', '') }}"></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-item" {{ count($invoice->items) == 1 ? 'disabled' : '' }}><i class="fas fa-times"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn"><i class="fas fa-plus me-1"></i> Add Item</button>
                    
                    <div class="mt-4">
                        <label class="form-label fw-semibold">Notes / Terms</label>
                        <textarea class="form-control" name="notes" rows="3">{{ $invoice->notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4 position-sticky" style="top: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 border-bottom pb-2">Summary</h5>
                    
                    <div class="mb-3 d-flex justify-content-between">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold" id="summarySubtotal">{{ $setting->currency }}0.00</span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold d-flex justify-content-between">
                            <span>{{ $setting->tax_name }} Rate (%)</span>
                            <span id="summaryTaxAmount">{{ $setting->currency }}0.00</span>
                        </label>
                        <input type="number" step="0.01" class="form-control" name="tax_rate" id="taxRateInput" value="{{ $invoice->tax_rate }}" required>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold fs-4 text-primary" id="summaryTotal">{{ $setting->currency }}0.00</span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold fs-5">Update Invoice</button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsTbody = document.getElementById('invoiceItems');
    const addItemBtn = document.getElementById('addItemBtn');
    const taxRateInput = document.getElementById('taxRateInput');
    let itemIndex = {{ count($invoice->items) }};

    function calculateTotals() {
        let subtotal = 0;
        const rows = itemsTbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const total = qty * price;
            
            row.querySelector('.item-total').value = total.toFixed(2);
            subtotal += total;
        });

        const taxRate = parseFloat(taxRateInput.value) || 0;
        const taxAmount = subtotal * (taxRate / 100);
        const finalTotal = subtotal + taxAmount;

        document.getElementById('summarySubtotal').innerText = '{{ $setting->currency }}' + subtotal.toFixed(2);
        document.getElementById('summaryTaxAmount').innerText = '{{ $setting->currency }}' + taxAmount.toFixed(2);
        document.getElementById('summaryTotal').innerText = '{{ $setting->currency }}' + finalTotal.toFixed(2);
    }

    addItemBtn.addEventListener('click', function() {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" class="form-control" name="items[${itemIndex}][description]" required placeholder="Item description"></td>
            <td><input type="number" class="form-control qty-input" name="items[${itemIndex}][quantity]" value="1" min="1" required></td>
            <td><input type="number" step="0.01" class="form-control price-input" name="items[${itemIndex}][price]" value="0.00" min="0" required></td>
            <td><input type="text" class="form-control item-total" readonly value="0.00"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-times"></i></button></td>
        `;
        itemsTbody.appendChild(tr);
        itemIndex++;
        
        const removeBtns = itemsTbody.querySelectorAll('.remove-item');
        removeBtns.forEach(btn => btn.disabled = false);
    });

    itemsTbody.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
            calculateTotals();
        }
    });

    itemsTbody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const row = e.target.closest('tr');
            row.remove();
            
            const rows = itemsTbody.querySelectorAll('tr');
            if (rows.length === 1) {
                rows[0].querySelector('.remove-item').disabled = true;
            }
            calculateTotals();
        }
    });

    taxRateInput.addEventListener('input', calculateTotals);
    
    // Initial calc
    calculateTotals();
});
</script>
@endpush
@endsection
