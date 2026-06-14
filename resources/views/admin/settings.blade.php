@extends('layouts.admin')

@section('title', 'Company Settings - InvoicePro')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-1">Company Settings</h2>
        <p class="text-muted mb-0">Configure your global invoice preferences.</p>
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

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Business Information</h5>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Company Logo</label>
                        @if(isset($setting->logo) && $setting->logo)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $setting->logo) }}" alt="Logo" class="img-thumbnail" style="max-height: 80px;">
                            </div>
                        @endif
                        <input class="form-control" type="file" name="logo" accept="image/*">
                        <div class="form-text">Upload a PNG, JPG, or GIF (max 2MB). Recommended height: 80px.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="company_name" value="{{ old('company_name', $setting->company_name) }}" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $setting->email) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" class="form-control" name="phone" value="{{ old('phone', $setting->phone) }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company Address</label>
                        <textarea class="form-control" name="address" rows="3">{{ old('address', $setting->address) }}</textarea>
                    </div>
                    
                    <h5 class="fw-bold mb-3 mt-5 border-bottom pb-2">Invoice Preferences</h5>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Currency Symbol <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="currency" value="{{ old('currency', $setting->currency ?? '$') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Tax Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="tax_name" value="{{ old('tax_name', $setting->tax_name ?? 'Tax') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Default Tax Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="default_tax_rate" value="{{ old('default_tax_rate', $setting->default_tax_rate ?? 0) }}" required>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Save Company Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="fas fa-robot me-2"></i>AI Integrations</h5>
                <p class="text-muted small mb-4">Connect to Google Gemini 2.5 Flash to unlock the ability to automatically extract invoice data from uploaded images.</p>
                
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Gemini API Key</label>
                        <input type="password" class="form-control" name="gemini_api_key" value="{{ old('gemini_api_key', $setting->gemini_api_key ?? '') }}" placeholder="AIzaSy...">
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-magic me-2"></i> Save AI Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
