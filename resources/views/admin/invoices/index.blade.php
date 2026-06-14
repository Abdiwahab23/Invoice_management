@extends('layouts.admin')

@section('title', 'Invoices - InvoicePro')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">Invoices</h2>
            <p class="text-muted mb-0">Manage and track your invoices.</p>
        </div>
        <div>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Create Invoice</a>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Invoice #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td class="ps-4 fw-bold text-primary"><a href="{{ route('invoices.show', $inv->id) }}" class="text-decoration-none">{{ $inv->invoice_number }}</a></td>
                        <td>{{ $inv->customer->customer_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('M d, Y') }}</td>
                        <td>
                            @php
                                $isOverdue = \Carbon\Carbon::parse($inv->due_date)->isPast() && $inv->status !== 'paid';
                            @endphp
                            <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                {{ \Carbon\Carbon::parse($inv->due_date)->format('M d, Y') }}
                                @if($isOverdue) <i class="fas fa-exclamation-circle ms-1" title="Overdue"></i> @endif
                            </span>
                        </td>
                        <td class="fw-semibold">{{ $setting->currency ?? '$' }}{{ number_format($inv->total_amount, 2) }}</td>
                        <td>
                            @if($inv->status == 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($inv->status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($inv->status == 'partial')
                                <span class="badge bg-info text-dark">Partial</span>
                            @else
                                <span class="badge bg-danger">Overdue</span>
                            @endif
                        </td>
                        <td class="text-center" style="white-space: nowrap;">
                            <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-sm btn-light text-primary" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('invoices.show', ['invoice' => $inv->id, 'action' => 'download']) }}" class="btn btn-sm btn-light text-success" title="Download PDF"><i class="fas fa-download"></i></a>
                            <form method="POST" action="{{ route('invoices.destroy', $inv->id) }}" class="d-inline" onsubmit="return confirm('Delete this invoice?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No invoices found. Click 'Create Invoice' to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
