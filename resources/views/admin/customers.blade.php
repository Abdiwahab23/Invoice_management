@extends('layouts.admin')

@section('title', 'Customers - InvoicePro')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">Customers</h2>
            <p class="text-muted mb-0">Manage your clients and customers.</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="fas fa-plus me-2"></i> Add Customer</button>
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

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Name</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                    <tr>
                        <td class="ps-4 fw-semibold text-primary">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle text-primary fw-bold d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($c->customer_name, 0, 1)) }}
                                </div>
                                {{ $c->customer_name }}
                            </div>
                        </td>
                        <td>{{ $c->company_name ?: '-' }}</td>
                        <td>{{ $c->email ?: '-' }}</td>
                        <td>{{ $c->phone ?: '-' }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-light text-primary edit-btn" title="Edit" 
                                data-id="{{ $c->id }}"
                                data-name="{{ $c->customer_name }}"
                                data-company="{{ $c->company_name }}"
                                data-email="{{ $c->email }}"
                                data-phone="{{ $c->phone }}"
                                data-address="{{ $c->address }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('customers.destroy', $c->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this customer? All their invoices will also be deleted.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No customers found. Click 'Add Customer' to create one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="customer_name" required placeholder="John Doe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company Name</label>
                        <input type="text" class="form-control" name="company_name" placeholder="ABC Corp">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required placeholder="john@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" class="form-control" name="phone" placeholder="+1 234 567 8900">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea class="form-control" name="address" rows="3" placeholder="123 Business Rd..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editCustomerForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="customer_name" id="edit_customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company Name</label>
                        <input type="text" class="form-control" name="company_name" id="edit_company_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" class="form-control" name="phone" id="edit_phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea class="form-control" name="address" id="edit_address" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-btn');
    if (editBtns.length > 0 && typeof bootstrap !== 'undefined') {
        const editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        
        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                // Dynamically update form action using a placeholder replacement approach
                let baseUrl = "{{ route('customers.update', 'ID_PLACEHOLDER') }}";
                document.getElementById('editCustomerForm').action = baseUrl.replace('ID_PLACEHOLDER', id);
                
                document.getElementById('edit_customer_name').value = this.getAttribute('data-name');
                document.getElementById('edit_company_name').value = this.getAttribute('data-company');
                document.getElementById('edit_email').value = this.getAttribute('data-email');
                document.getElementById('edit_phone').value = this.getAttribute('data-phone');
                document.getElementById('edit_address').value = this.getAttribute('data-address');
                editModal.show();
            });
        });
    }
});
</script>
@endpush
@endsection
