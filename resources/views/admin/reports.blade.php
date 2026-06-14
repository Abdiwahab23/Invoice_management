@extends('layouts.admin')

@section('title', 'Reports & Analytics - InvoicePro')

@section('content')
<div class="row mb-4 screen-header">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">Reports & Analytics</h2>
            <p class="text-muted mb-0">Detailed insights into your invoicing and revenue.</p>
        </div>
        <div>
            <button onclick="downloadReport()" class="btn btn-outline-primary" id="downloadBtn"><i class="fas fa-download me-2"></i> Download PDF</button>
        </div>
    </div>
</div>

<div id="report-content">
    <!-- Print-only Header (will be visible in PDF) -->
    <div class="report-print-header">
        @php $globalSetting = \App\Models\CompanySetting::first(); @endphp
        @if(isset($globalSetting->logo) && $globalSetting->logo)
            <img src="{{ asset('storage/' . $globalSetting->logo) }}" alt="Logo" style="height: 50px; margin-bottom: 15px;">
        @endif
        <h1>Financial Analytics Report</h1>
        <p>Generated on {{ date('F d, Y, g:i a') }} | {{ $globalSetting->company_name ?? 'InvoicePro' }}</p>
    </div>

    <!-- Global Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="fw-semibold text-white-50 mb-1" style="font-size: 0.85rem;">Lifetime revenue</div>
                    <h2 class="mb-0 fw-bold">{{ $setting->currency ?? '$' }}{{ number_format($totalRevenue, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <div class="fw-semibold text-white-50 mb-1" style="font-size: 0.85rem;">Total paid invoices</div>
                    <h2 class="mb-0 fw-bold">{{ number_format($totalPaidInvoices) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white h-100">
                <div class="card-body">
                    <div class="fw-semibold text-white-50 mb-1" style="font-size: 0.85rem;">Unpaid / pending invoices</div>
                    <h2 class="mb-0 fw-bold">{{ number_format($totalUnpaidInvoices) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 fw-bold"><i class="fas fa-chart-bar text-primary me-2"></i> Revenue Overview ({{ date('Y') }})</div>
                <div class="card-body">
                    <canvas id="yearlyRevenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 fw-bold"><i class="fas fa-chart-pie text-info me-2"></i> Invoice Status Distribution</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="reportsStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold"><i class="fas fa-trophy text-warning me-2"></i> Top Customers
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Customer</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers as $customer)
                                    <tr>
                                        <td class="ps-4 fw-bold text-truncate" style="max-width: 150px;">{{ $customer->customer_name }}</td>
                                        <td class="fw-semibold text-success" style="white-space: nowrap;">
                                            {{ $setting->currency ?? '$' }}{{ number_format($customer->invoices_sum_total_amount ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-3 text-muted">No data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold"><i class="fas fa-history text-secondary me-2"></i> Recent Payment History</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Invoice</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                    <tr>
                                        <td class="ps-4" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                        </td>
                                        <td style="white-space: nowrap;">{{ $payment->invoice->invoice_number }}</td>
                                        <td class="fw-semibold text-success" style="white-space: nowrap;">+{{ $setting->currency ?? '$' }}{{ number_format($payment->amount, 2) }}</td>
                                        <td><span
                                                class="badge bg-light text-dark border text-truncate" style="max-width: 100px; display: inline-block;">{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No recent payments.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Last 5 Invoices -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 fw-bold"><i class="fas fa-file-invoice text-primary me-2"></i> Last 5 Generated Invoices</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Invoice #</th>
                                    <th>Customer</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($last5Invoices as $invoice)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->customer->customer_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</td>
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
                                        <td colspan="6" class="text-center py-3 text-muted">No invoices generated yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End report-content -->
@endsection

@push('scripts')
<style>
    /* Styling for the hidden print header that shows up in the PDF */
    .report-print-header {
        display: none;
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #1e293b;
        padding-bottom: 15px;
    }
    .report-print-header h1 {
        font-size: 24px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 5px;
        text-transform: uppercase;
    }
    .report-print-header p {
        font-size: 14px;
        color: #64748b;
        margin: 0;
    }
    
    /* PDF specific adjustments class applied before capturing */
    .pdf-mode {
        padding: 20px;
        background: #fff;
    }
    .pdf-mode .report-print-header {
        display: block;
    }
    .pdf-mode .screen-header {
        display: none;
    }
    .pdf-mode .card {
        box-shadow: none !important;
        border: none !important;
        margin-bottom: 20px !important;
    }
    .pdf-mode canvas {
        max-height: 200px !important;
    }
    .pdf-mode .row {
        margin-bottom: 10px !important;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// PDF Download Function
function downloadReport() {
    var btn = document.getElementById('downloadBtn');
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating PDF...';
    btn.disabled = true;

    var element = document.getElementById('report-content');
    element.classList.add('pdf-mode');

    var opt = {
        margin:       10,
        filename:     'Financial_Report_{{ date("Y_m") }}.pdf',
        image:        { type: 'jpeg', quality: 1.0 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(function() {
        element.classList.remove('pdf-mode');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Yearly Revenue Chart
    var ctxRev = document.getElementById('yearlyRevenueChart').getContext('2d');
    new Chart(ctxRev, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Monthly Revenue',
                data: {!! json_encode($revenueData) !!},
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderColor: '#0d6efd',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function (value) { return '{{ $setting->currency ?? "$" }}' + value; } } }
            }
        }
    });

    // Reports Status Chart
    var ctxStat = document.getElementById('reportsStatusChart').getContext('2d');

    // Prepare data safely
    var statuses = {!! json_encode($statusSummary) !!};
    var dataMap = { paid: 0, pending: 0, overdue: 0, partial: 0 };
    statuses.forEach(function (item) {
        if (dataMap[item.status] !== undefined) {
            dataMap[item.status] = item.count;
        }
    });

    new Chart(ctxStat, {
        type: 'pie',
        data: {
            labels: ['Paid', 'Pending', 'Overdue', 'Partial'],
            datasets: [{
                data: [dataMap.paid, dataMap.pending, dataMap.overdue, dataMap.partial],
                backgroundColor: ['#198754', '#ffc107', '#dc3545', '#0dcaf0'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
@endpush