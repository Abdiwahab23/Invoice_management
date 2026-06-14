<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'InvoicePro - Dashboard')</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
    <script>
        // Apply theme immediately to prevent flashing
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        #wrapper { display: flex; min-height: 100vh; overflow-x: hidden; }
        #sidebar-wrapper { min-width: 250px; max-width: 250px; transition: margin 0.25s ease-out; }
        #page-content-wrapper { min-width: 100vw; transition: margin 0.25s ease-out; }
        @media (min-width: 768px) {
            #page-content-wrapper { min-width: 0; width: 100%; }
        }
        #wrapper.toggled #sidebar-wrapper { margin-left: -250px; }
        .sidebar-link { background-color: transparent !important; border: none !important; transition: all 0.2s; }
        .sidebar-link:hover { background-color: #1b263b !important; }
        .sidebar-link.active { background-color: #1b263b !important; border-left: 4px solid #0ea5e9 !important; }
        .widget-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 3rem; opacity: 0.2; }
        
        /* Dark Mode Enhancements */
        [data-bs-theme="dark"] body { background-color: #121212 !important; color: #e0e0e0 !important; }
        [data-bs-theme="dark"] .bg-white { background-color: #1e1e1e !important; color: #e0e0e0 !important; }
        [data-bs-theme="dark"] .bg-light { background-color: #2a2a2a !important; color: #e0e0e0 !important; }
        [data-bs-theme="dark"] .text-dark { color: #e0e0e0 !important; }
        [data-bs-theme="dark"] .text-muted { color: #9e9e9e !important; }
        [data-bs-theme="dark"] .table-light { background-color: #2a2a2a !important; color: #e0e0e0 !important; --bs-table-bg: #2a2a2a; --bs-table-color: #e0e0e0; border-color: #444; }
        [data-bs-theme="dark"] .table { --bs-table-color: #e0e0e0; --bs-table-border-color: #444; }
        [data-bs-theme="dark"] .card { background-color: #1e1e1e !important; border-color: #333 !important; }
        [data-bs-theme="dark"] .border, [data-bs-theme="dark"] .border-bottom, [data-bs-theme="dark"] .border-top { border-color: #333 !important; }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select, [data-bs-theme="dark"] .input-group-text { background-color: #2a2a2a !important; border-color: #444 !important; color: #e0e0e0 !important; }
        [data-bs-theme="dark"] .form-control:focus, [data-bs-theme="dark"] .form-select:focus { background-color: #333 !important; border-color: #0ea5e9 !important; color: #fff !important; }
        [data-bs-theme="dark"] .modal-content { background-color: #1e1e1e !important; }
        [data-bs-theme="dark"] .dropdown-menu { background-color: #1e1e1e !important; border-color: #333 !important; }
        [data-bs-theme="dark"] .dropdown-item { color: #e0e0e0 !important; }
        [data-bs-theme="dark"] .dropdown-item:hover { background-color: #2a2a2a !important; }
        [data-bs-theme="dark"] .nav-link { color: #e0e0e0 !important; }
        [data-bs-theme="dark"] .navbar { background-color: #1e1e1e !important; }
        
        /* Search Styling */
        .search-header { background-color: #f8f9fa; }
        [data-bs-theme="dark"] #searchResults { background-color: #1e1e1e !important; border-color: #333 !important; }
        [data-bs-theme="dark"] .search-header { background-color: #2a2a2a !important; color: #9e9e9e !important; border-color: #333 !important; }
        [data-bs-theme="dark"] .search-result-item:hover { background-color: #2a2a2a !important; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="text-white shadow-sm" id="sidebar-wrapper" style="background-color: #0d1b2a !important; overflow: hidden; border-right: 1px solid #1b263b;">
            <div class="sidebar-heading py-4 fs-5 fw-bold border-bottom border-secondary d-flex align-items-center justify-content-center px-3">
                @php $globalSetting = \App\Models\CompanySetting::first(); @endphp
                @if(isset($globalSetting->logo) && $globalSetting->logo)
                    <img src="{{ asset('storage/' . $globalSetting->logo) }}" alt="Logo" style="max-height: 32px; object-fit: contain; margin-right: 10px;">
                @else
                    <i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>
                @endif
                <span class="text-truncate">{{ $globalSetting->company_name ?? 'InvoicePro' }}</span>
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action text-white sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action text-white sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"><i class="fas fa-users me-2"></i> Customers</a>
                <a href="{{ route('invoices.index') }}" class="list-group-item list-group-item-action text-white sidebar-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}"><i class="fas fa-file-invoice me-2"></i> Invoices</a>
                <a href="{{ route('payments.index') }}" class="list-group-item list-group-item-action text-white sidebar-link {{ request()->routeIs('payments.*') ? 'active' : '' }}"><i class="fas fa-money-bill-wave me-2"></i> Payments</a>
                <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action text-white sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="fas fa-chart-line me-2"></i> Reports</a>
                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action text-white sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="fas fa-user-shield me-2"></i> Users</a>
                <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action text-white sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="fas fa-cogs me-2"></i> Company Settings</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-5">
                    @csrf
                    <button type="submit" class="list-group-item list-group-item-action text-danger sidebar-link" style="width: 100%; text-align: left;"><i class="fas fa-sign-out-alt me-2"></i> Logout</button>
                </form>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-outline-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>
                    
                    <form class="d-flex ms-3 w-50 position-relative" id="globalSearchForm" onsubmit="return false;">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input class="form-control border-start-0 bg-light" id="globalSearchInput" type="search" placeholder="Search invoices, customers..." aria-label="Search" autocomplete="off">
                        </div>
                        <div id="searchResults" class="position-absolute w-100 bg-white shadow-lg rounded mt-1 d-none" style="top: 100%; z-index: 1050; max-height: 400px; overflow-y: auto; border: 1px solid rgba(0,0,0,0.1);">
                        </div>
                    </form>

                    <div class="ms-auto d-flex align-items-center">
                        <button class="btn btn-link nav-link me-3 text-secondary" id="theme-toggle" title="Toggle Theme" style="text-decoration: none;">
                            <i class="fas fa-moon fs-5" id="theme-icon"></i>
                        </button>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="rounded-circle me-2 object-fit-cover" width="32" height="32" alt="Avatar">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0d6efd&color=fff" class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                @endif
                                <span class="fw-bold">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item text-danger" type="submit"><i class="fas fa-sign-out-alt me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            <div class="container-fluid px-4 py-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
    <script>
        document.getElementById('menu-toggle').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('wrapper').classList.toggle('toggled');
        });

        // Theme Toggle Logic
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const htmlElement = document.documentElement;

        function updateThemeIcon() {
            if (htmlElement.getAttribute('data-bs-theme') === 'dark') {
                themeIcon.classList.remove('fa-moon', 'text-secondary');
                themeIcon.classList.add('fa-sun', 'text-warning');
            } else {
                themeIcon.classList.remove('fa-sun', 'text-warning');
                themeIcon.classList.add('fa-moon', 'text-secondary');
            }
        }
        updateThemeIcon();

        themeToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
        });

        // Global Smart Search
        const searchInput = document.getElementById('globalSearchInput');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    searchResults.classList.add('d-none');
                    return;
                }

                searchTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`{{ route('search') }}?q=${encodeURIComponent(query)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        
                        let html = '';
                        
                        if (data.customers.length > 0) {
                            html += '<div class="px-3 py-2 search-header fw-bold small text-muted border-bottom">CUSTOMERS</div>';
                            data.customers.forEach(c => {
                                html += `<a href="{{ route('customers.index') }}" class="dropdown-item py-2 search-result-item"><i class="fas fa-user text-secondary me-2"></i>${c.customer_name} <small class="text-muted d-block ms-4">${c.email || ''}</small></a>`;
                            });
                        }
                        
                        if (data.invoices.length > 0) {
                            html += '<div class="px-3 py-2 search-header fw-bold small text-muted border-bottom">INVOICES</div>';
                            data.invoices.forEach(i => {
                                html += `<a href="{{ url('invoices') }}/${i.id}" class="dropdown-item py-2 search-result-item d-flex align-items-center justify-content-between"><div class="text-truncate"><i class="fas fa-file-invoice text-secondary me-2"></i>${i.invoice_number} <small class="text-muted ms-2">${i.customer.customer_name}</small></div><span class="fw-bold text-success ms-3">{{ $globalSetting->currency ?? "$" }}${parseFloat(i.total_amount).toFixed(2)}</span></a>`;
                            });
                        }
                        
                        if (html === '') {
                            html = '<div class="px-3 py-3 text-muted text-center">No results found for "'+query+'"</div>';
                        }
                        
                        searchResults.innerHTML = html;
                        searchResults.classList.remove('d-none');
                        
                    } catch (error) {
                        console.error('Search error:', error);
                    }
                }, 300);
            });

            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('d-none');
                }
            });
            
            // Re-open when focusing
            searchInput.addEventListener('focus', function() {
                if (this.value.trim().length >= 2 && searchResults.innerHTML !== '') {
                    searchResults.classList.remove('d-none');
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
