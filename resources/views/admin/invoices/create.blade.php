@extends('layouts.admin')

@section('title', 'Create Invoice - InvoicePro')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">Create Invoice</h2>
            <a href="{{ route('invoices.index') }}" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Back to Invoices</a>
        </div>
        <div>
            <button type="button" class="btn btn-outline-info shadow-sm fw-bold" onclick="document.getElementById('aiUploadInput').click()">
                <i class="fas fa-magic me-1"></i> Auto-fill with AI
            </button>
            <input type="file" id="aiUploadInput" class="d-none" accept="image/*,application/pdf">
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

<form method="POST" action="{{ route('invoices.store') }}">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 border-bottom pb-2">Invoice Details</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select" name="customer_id" id="customer_id" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#quickAddCustomerModal" title="Add New Customer">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="invoice_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="due_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required>
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
                                <tr>
                                    <td><input type="text" class="form-control" name="items[0][description]" required placeholder="Web Design Services"></td>
                                    <td><input type="number" class="form-control qty-input" name="items[0][quantity]" value="1" min="1" required></td>
                                    <td><input type="number" step="0.01" class="form-control price-input" name="items[0][price]" value="0.00" min="0" required></td>
                                    <td><input type="text" class="form-control item-total" readonly value="0.00"></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-item" disabled><i class="fas fa-times"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn"><i class="fas fa-plus me-1"></i> Add Item</button>
                    
                    <div class="mt-4">
                        <label class="form-label fw-semibold">Notes / Terms</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Thank you for your business."></textarea>
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
                        <input type="number" step="0.01" class="form-control" name="tax_rate" id="taxRateInput" value="{{ $setting->default_tax_rate }}" required>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold fs-4 text-primary" id="summaryTotal">{{ $setting->currency }}0.00</span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold fs-5">Save Invoice</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Quick Add Customer Modal -->
<div class="modal fade" id="quickAddCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Quick Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickAddCustomerForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="customer_name" id="qa_customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address (Optional)</label>
                        <input type="email" class="form-control" name="email" id="qa_email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone (Optional)</label>
                        <input type="text" class="form-control" name="phone" id="qa_phone">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="qa_submit_btn">Add Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsTbody = document.getElementById('invoiceItems');
    const addItemBtn = document.getElementById('addItemBtn');
    const taxRateInput = document.getElementById('taxRateInput');
    let itemIndex = 1;

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

    // AI Extraction Logic
    const aiUploadInput = document.getElementById('aiUploadInput');
    if (aiUploadInput) {
        aiUploadInput.addEventListener('change', async function() {
            if (!this.files || !this.files[0]) return;
            
            const btn = document.querySelector('button[onclick="document.getElementById(\'aiUploadInput\').click()"]');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Analyzing Document...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('invoice_file', this.files[0]);

            try {
                const response = await fetch('{{ route("invoices.extract") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();
                
                if (!response.ok) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Extraction Failed',
                        text: data.error || 'Failed to extract data.'
                    });
                    return;
                }

                // Confirm before auto-filling
                Swal.fire({
                    icon: 'question',
                    title: 'Confirm AI Extraction',
                    html: 'AI detected customer: <strong>' + (data.customer_name ? data.customer_name : 'Unknown') + '</strong><br>' +
                          'Invoice Date: ' + (data.invoice_date || 'N/A') + '<br>' +
                          'Found ' + (data.items ? data.items.length : 0) + ' item(s).<br><br>' +
                          'Do you want to apply this data to the form?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, apply it!',
                    cancelButtonText: 'Discard'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Auto-fill dates
                        if (data.invoice_date) document.querySelector('input[name="invoice_date"]').value = data.invoice_date;
                        if (data.due_date) document.querySelector('input[name="due_date"]').value = data.due_date;

                        // Auto-fill customer
                        if (data.customer_name) {
                            const select = document.querySelector('select[name="customer_id"]');
                            const options = Array.from(select.options);
                            const match = options.find(opt => opt.text.toLowerCase().includes(data.customer_name.toLowerCase()) || data.customer_name.toLowerCase().includes(opt.text.toLowerCase()));
                            if (match) {
                                select.value = match.value;
                            } else {
                                setTimeout(() => {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Customer Not Found',
                                        text: 'AI extracted customer name "' + data.customer_name + '" but it does not match any existing customers. Please select or create the customer manually.'
                                    });
                                }, 500);
                            }
                        }

                        // Auto-fill items
                        if (data.items && data.items.length > 0) {
                            itemsTbody.innerHTML = '';
                            itemIndex = 0;
                            data.items.forEach(item => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td><input type="text" class="form-control" name="items[${itemIndex}][description]" required value="${item.description || ''}"></td>
                                    <td><input type="number" class="form-control qty-input" name="items[${itemIndex}][quantity]" value="${item.quantity || 1}" min="1" required></td>
                                    <td><input type="number" step="0.01" class="form-control price-input" name="items[${itemIndex}][price]" value="${item.price || 0}" min="0" required></td>
                                    <td><input type="text" class="form-control item-total" readonly value="0.00"></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-times"></i></button></td>
                                `;
                                itemsTbody.appendChild(tr);
                                itemIndex++;
                            });
                            calculateTotals();
                            
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            Toast.fire({
                                icon: 'success',
                                title: 'Form auto-filled successfully!'
                            });
                        }
                    }
                });

            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'An error occurred during AI extraction.'
                });
                console.error(err);
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                aiUploadInput.value = '';
            }
        });
    }

    // Quick Add Customer AJAX
    const quickAddCustomerForm = document.getElementById('quickAddCustomerForm');
    if (quickAddCustomerForm) {
        quickAddCustomerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('qa_submit_btn');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
            btn.disabled = true;

            const formData = new FormData(this);
            
            try {
                const response = await fetch('{{ route("customers.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: data.message || 'Please check your inputs and try again.'
                    });
                    return;
                }

                if (data.success && data.customer) {
                    // Add to dropdown
                    const select = document.getElementById('customer_id');
                    const option = document.createElement('option');
                    option.value = data.customer.id;
                    option.text = data.customer.customer_name;
                    select.add(option);
                    select.value = data.customer.id; // Auto select

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('quickAddCustomerModal'));
                    modal.hide();

                    // Reset form
                    this.reset();

                    // Toast success
                    Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    }).fire({
                        icon: 'success',
                        title: 'Customer added successfully!'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'System Error',
                    text: 'Could not add customer.'
                });
                console.error(err);
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        });
    }
});
</script>
@endpush
@endsection
