@extends('layouts.admin')

@section('title', 'Dashboard Overview - InvoicePro')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">Dashboard Overview</h2>
            <p class="text-muted mb-0">Welcome back, here is the overview of your business.</p>
        </div>
        <div>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Create Invoice</a>
        </div>
    </div>
</div>

<!-- Widgets -->
<div class="row g-4 mb-5">
    <div class="col-md-4 col-xl-2">
        <div class="card bg-gradient-primary text-white h-100 position-relative overflow-hidden" style="background-color: #0d6efd !important;">
            <div class="card-body">
                <div class="mb-1 fw-semibold opacity-75" style="font-size: 0.85rem;">Customers</div>
                <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;">{{ number_format($stats['total_customers']) }}</h5>
                <div class="widget-icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card bg-info text-white h-100 position-relative overflow-hidden" style="background: linear-gradient(45deg, #0dcaf0, #0aa2c0) !important;">
            <div class="card-body">
                <div class="mb-1 fw-semibold opacity-75" style="font-size: 0.85rem;">Invoices</div>
                <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;">{{ number_format($stats['total_invoices']) }}</h5>
                <div class="widget-icon"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card bg-gradient-success text-white h-100 position-relative overflow-hidden" style="background-color: #198754 !important;">
            <div class="card-body">
                <div class="mb-1 fw-semibold opacity-75" style="font-size: 0.85rem;">Paid Invoices</div>
                <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;">{{ number_format($stats['paid_invoices']) }}</h5>
                <div class="widget-icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card bg-gradient-warning text-dark h-100 position-relative overflow-hidden" style="background-color: #ffc107 !important;">
            <div class="card-body">
                <div class="mb-1 fw-semibold opacity-75" style="font-size: 0.85rem;">Pending</div>
                <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;">{{ number_format($stats['pending_invoices']) }}</h5>
                <div class="widget-icon"><i class="fas fa-clock text-dark"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card bg-dark text-white h-100 position-relative overflow-hidden" style="background: linear-gradient(45deg, #212529, #343a40) !important;">
            <div class="card-body">
                <div class="mb-1 fw-semibold opacity-75" style="font-size: 0.85rem;">Revenue (M)</div>
                <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;">{{ $setting->currency ?? '$' }}{{ number_format($stats['monthly_revenue'], 2) }}</h5>
                <div class="widget-icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card bg-gradient-danger h-100 position-relative overflow-hidden" style="background-color: #dc3545 !important;">
            <div class="card-body">
                <div class="mb-1 fw-semibold opacity-75" style="font-size: 0.85rem;">Unpaid Balance</div>
                <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;">{{ $setting->currency ?? '$' }}{{ number_format($stats['outstanding_balance'], 2) }}</h5>
                <div class="widget-icon"><i class="fas fa-exclamation-circle text-danger"></i></div>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Charts Section -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3">
                <i class="fas fa-chart-area me-2 text-primary"></i> Revenue Over Time (Last 6 Months)
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3">
                <i class="fas fa-chart-pie me-2 text-info"></i> Invoice Status Overview
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tables Section -->
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                <span class="fs-5"><i class="fas fa-file-alt me-2 text-primary"></i> Recent Invoices</span>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Invoice #</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInvoices as $invoice)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->customer->customer_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</td>
                                <td class="fw-semibold">{{ $setting->currency ?? '$' }}{{ number_format($invoice->total_amount, 2) }}</td>
                                <td>
                                    @if($invoice->status == 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($invoice->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($invoice->status == 'partial')
                                        <span class="badge bg-info text-dark">Partial</span>
                                    @else
                                        <span class="badge bg-danger">Overdue</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No recent invoices found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold py-3">
                <i class="fas fa-money-check-alt me-2 text-success"></i> Recent Payments
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentPayments as $payment)
                    <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">{{ $payment->invoice->customer->customer_name }}</div>
                            <div class="text-muted small">{{ $payment->invoice->invoice_number }} • {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d') }}</div>
                        </div>
                        <div class="fw-bold text-success">+{{ $setting->currency ?? '$' }}{{ number_format($payment->amount, 2) }}</div>
                    </li>
                    @empty
                    <li class="list-group-item text-center py-4 text-muted">No recent payments.</li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer bg-white text-center">
                <a href="#" class="text-decoration-none small fw-bold">View All Payments <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    var ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    var gradient = ctxRevenue.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(13, 110, 253, 0.5)');   
    gradient.addColorStop(1, 'rgba(13, 110, 253, 0.0)');

    var revenueChart = new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['months']) !!}.reverse(),
            datasets: [{
                label: 'Monthly Revenue',
                data: {!! json_encode($chartData['revenue']) !!}.reverse(),
                backgroundColor: gradient,
                borderColor: '#0d6efd',
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#0d6efd',
                pointBorderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return '{{ $setting->currency ?? "$" }}' + value; }
                    }
                }
            }
        }
    });

    // Invoice Status Chart
    var ctxStatus = document.getElementById('statusChart').getContext('2d');
    var statusChart = new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Pending', 'Overdue', 'Partial'],
            datasets: [{
                data: [
                    {{ $stats['paid_invoices'] }}, 
                    {{ $stats['pending_invoices'] }}, 
                    {{ $stats['overdue_invoices'] }},
                    {{ $stats['partial_invoices'] }}
                ],
                backgroundColor: ['#198754', '#ffc107', '#dc3545', '#0dcaf0'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 20 }
                }
            }
        }
    });
});
</script>
@endpush
