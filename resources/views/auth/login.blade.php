<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InvoicePro</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.175);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            background: #fff;
        }
        .login-image {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 3rem;
            flex-direction: column;
            text-align: center;
        }
        .login-image i {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
        .btn-primary {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .input-group-text {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }
        .form-control.border-start-0 {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
    </style>
</head>
<body>
    @php 
        try {
            $globalSetting = \App\Models\CompanySetting::first(); 
        } catch (\Exception $e) {
            $globalSetting = null;
        }
    @endphp
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="card login-card flex-md-row">
                    <!-- Left Side: Image / Branding -->
                    <div class="col-md-5 d-none d-md-flex login-image">
                        @if(isset($globalSetting->logo) && $globalSetting->logo)
                            <img src="{{ asset('storage/' . $globalSetting->logo) }}" alt="Logo" class="mb-4 shadow-sm" style="max-height: 80px; object-fit: contain; border-radius: 10px;">
                        @else
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        @endif
                        <h3 class="fw-bold">{{ $globalSetting->company_name ?? 'InvoicePro' }}</h3>
                        <p class="opacity-75">Manage your invoices, customers, and payments effortlessly in one centralized platform.</p>
                    </div>
                    
                    <!-- Right Side: Form -->
                    <div class="col-12 col-md-7 p-4 p-md-5">
                        <div class="text-center mb-4 d-md-none">
                            @if(isset($globalSetting->logo) && $globalSetting->logo)
                                <img src="{{ asset('storage/' . $globalSetting->logo) }}" alt="Logo" style="max-height: 50px;">
                            @else
                                <i class="fa-solid fa-file-invoice-dollar text-primary fs-1 mb-2"></i>
                            @endif
                            <h4 class="fw-bold">{{ $globalSetting->company_name ?? 'InvoicePro' }}</h4>
                        </div>
                        
                        <h2 class="fw-bold mb-1 text-dark">Welcome Back</h2>
                        <p class="text-muted mb-4">Please log in to your account.</p>

                        <!-- Session Status -->
                        @if (session('status'))
                            <div class="alert alert-success rounded-3 mb-4">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3 mb-4">
                                <ul class="mb-0 px-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold text-dark">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input id="email" type="email" class="form-control border-start-0 bg-light" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold text-dark">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input id="password" type="password" class="form-control border-start-0 bg-light" name="password" required autocomplete="current-password" placeholder="••••••••">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                                    <label class="form-check-label text-muted" for="remember_me">
                                        Remember me
                                    </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-decoration-none text-primary fw-semibold small">Forgot Password?</a>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-primary w-100 shadow-sm py-2 fs-5">
                                <i class="fas fa-sign-in-alt me-2"></i> Log in
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
