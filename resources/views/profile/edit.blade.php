@extends('layouts.admin')

@section('title', 'Profile - InvoicePro')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-1">Profile</h2>
        <p class="text-muted mb-0">Update your account information and password.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Update Profile Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Profile Information</h5>
                <p class="text-muted small mb-4">Update your account's profile information and email address.</p>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('patch')
                    
                    <div class="mb-4 d-flex align-items-center">
                        <div class="me-3">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="rounded-circle object-fit-cover shadow-sm" style="width: 80px; height: 80px;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="form-label fw-semibold mb-1">Profile Photo (Logo)</label>
                            <input type="file" class="form-control form-control-sm @error('avatar') is-invalid @enderror" name="avatar" accept="image/*">
                            <div class="form-text">Square images (JPEG, PNG) look best. Max 2MB.</div>
                            @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-2 text-warning small">
                                Your email address is unverified.
                                <button form="send-verification" class="btn btn-link p-0 m-0 align-baseline text-warning fw-bold">Click here to re-send the verification email.</button>
                            </div>
                            @if (session('status') === 'verification-link-sent')
                                <div class="mt-2 text-success small">A new verification link has been sent to your email address.</div>
                            @endif
                        @endif
                    </div>

                    <div class="d-flex align-items-center">
                        <button type="submit" class="btn btn-primary px-4">Save</button>
                        @if (session('status') === 'profile-updated')
                            <span class="text-success ms-3 fw-semibold"><i class="fas fa-check-circle me-1"></i> Saved.</span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Password -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Update Password</h5>
                <p class="text-muted small mb-4">Ensure your account is using a long, random password to stay secure.</p>

                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" required>
                        @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center">
                        <button type="submit" class="btn btn-primary px-4">Save</button>
                        @if (session('status') === 'password-updated')
                            <span class="text-success ms-3 fw-semibold"><i class="fas fa-check-circle me-1"></i> Saved.</span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Account -->
        <div class="card border-0 shadow-sm mb-4 border-danger border-top border-3">
            <div class="card-body p-4">
                <h5 class="fw-bold text-danger mb-1">Delete Account</h5>
                <p class="text-muted small mb-4">Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.</p>

                <button class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">Delete Account</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="confirmUserDeletionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold text-danger">Are you sure you want to delete your account?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <p class="text-muted">Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.</p>
                    <div class="mb-3">
                        <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" name="password" placeholder="Password" required>
                        @error('password', 'userDeletion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->userDeletion->isNotEmpty())
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var myModal = new bootstrap.Modal(document.getElementById('confirmUserDeletionModal'));
        myModal.show();
    });
</script>
@endpush
@endif
@endsection
