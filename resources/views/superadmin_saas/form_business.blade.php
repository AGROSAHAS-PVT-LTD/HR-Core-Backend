@php
    use App\Models\Settings;
    $title = $business ? 'Edit Business | Super Admin' : 'Create Business | Super Admin';
    $settings = Settings::first();
@endphp

@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    @include('nav')
    
    <div class="container-fluid px-4 py-3">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-primary">{{ $title }}</h4>
            <a href="{{ route('superadmin.businesses') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Businesses
            </a>
        </div>

        <!-- Form Container -->
        <form action="{{ route($business ? 'superadmin.businesses.update' : 'superadmin.businesses.store', $business ? $business->id : '') }}" 
              method="post" enctype="multipart/form-data" class="needs-validation" >
            @csrf
            @if ($business)
                @method('PUT')
            @endif
            
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <h5 class="alert-heading">Please fix the following errors:</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Business Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info bg-opacity-10 mb-2">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i> Business Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Business Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label">Business Name <span class="text-danger">*</span></label>
                            <input id="name" name="name" class="form-control" 
                                   value="{{ old('name', $business->name ?? '') }}" required>
                            <div class="invalid-feedback">Please provide a business name.</div>
                        </div>

                        <!-- Business Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">Business Email <span class="text-danger">*</span></label>
                            <input id="email" name="email" type="email" class="form-control" 
                                   value="{{ old('email', $business->email ?? '') }}" required>
                            <div class="invalid-feedback">Please provide a valid email address.</div>
                        </div>

                        <!-- Business Contact -->
                        <div class="col-md-6">
                            <label for="contact" class="form-label">Business Contact</label>
                            <input id="contact" name="contact" class="form-control" 
                                   value="{{ old('contact', $business->contact ?? '') }}">
                        </div>

                        <!-- Country -->
                        <div class="col-md-6">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input id="country" name="country" class="form-control" 
                                   value="{{ old('country', $business->country ?? '') }}" required>
                            <div class="invalid-feedback">Please provide a country.</div>
                        </div>

                        <!-- City -->
                        <div class="col-md-6">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input id="city" name="city" class="form-control" 
                                   value="{{ old('city', $business->city ?? '') }}" required>
                            <div class="invalid-feedback">Please provide a city.</div>
                        </div>

                        <!-- Website -->
                        <div class="col-md-6">
                            <label for="website" class="form-label">Website</label>
                            <input id="website" name="website" type="url" class="form-control" 
                                   value="{{ old('website', $business->website ?? '') }}">
                            <div class="invalid-feedback">Please provide a valid URL.</div>
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input id="start_date" name="start_date" type="date" class="form-control" 
                                   value="{{ old('start_date', $business->start_date ?? '') }}">
                        </div>

                        <!-- Address -->
                        <div class="col-md-6">
                            <label for="address" class="form-label">Address</label>
                            <input id="address" name="address" class="form-control" 
                                   value="{{ old('address', $business->address ?? '') }}">
                        </div>

                        <!-- Logo -->
                        <div class="col-md-6">
                            <label for="logo" class="form-label">Business Logo</label>
                            <input id="logo" name="logo" type="file" class="form-control">
                            @if($business && $business->logo)
                                <div class="mt-2">
                                    <small>Current logo:</small>
                                    <img src="{{ asset('storage/'.$business->logo) }}" alt="Business Logo" class="img-thumbnail mt-1" style="max-height: 50px;">
                                </div>
                            @endif
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label">Business Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $business->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info bg-opacity-10 mb-2">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i> Subscription Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Package -->
                        <div class="col-md-6">
                            <label for="package" class="form-label">Subscription Package <span class="text-danger">*</span></label>
                            <select id="package" name="package" class="form-select" required>
                                <option value="">Select a Package</option>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}" {{ old('package', $business->package_id ?? '') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }} - {{ $settings->currency_symbol }}{{ number_format($package->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a package.</div>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-md-6">
                            <label for="paid_via" class="form-label">Paid Via <span class="text-danger">*</span></label>
                            <select id="paid_via" name="paid_via" class="form-select" required>
                                <option value="">Select Payment Method</option>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method }}" {{ old('paid_via', $business->paid_via ?? '') == $method ? 'selected' : '' }}>
                                        {{ $method }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a payment method.</div>
                        </div>

                        <!-- Transaction ID -->
                        <div class="col-12">
                            <label for="transaction_id" class="form-label">Transaction ID <span class="text-danger">*</span></label>
                            <input id="transaction_id" name="transaction_id" class="form-control" 
                                   value="{{ old('transaction_id', $business->transaction_id ?? '') }}" required>
                            <div class="invalid-feedback">Please provide a transaction ID.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Owner Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info bg-opacity-10 mb-2">
                    <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i> Owner Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- First Name -->
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input id="first_name" name="first_name" class="form-control" 
                                   value="{{ old('first_name', $user->first_name ?? '') }}" required>
                            <div class="invalid-feedback">Please provide the owner's first name.</div>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input id="last_name" name="last_name" class="form-control" 
                                   value="{{ old('last_name', $user->last_name ?? '') }}" required>
                            <div class="invalid-feedback">Please provide the owner's last name.</div>
                        </div>

                        <!-- Username -->
                        <div class="col-md-6">
                            <label for="user_name" class="form-label">Username <span class="text-danger">*</span></label>
                            <input id="user_name" name="user_name" class="form-control" 
                                   value="{{ old('user_name', $user->user_name ?? '') }}" required>
                            <div class="invalid-feedback">Please provide a username.</div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="user_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input id="user_email" name="user_email" type="email" class="form-control" 
                                   value="{{ old('user_email', $user->email ?? '') }}" required>
                            <div class="invalid-feedback">Please provide a valid email address.</div>
                        </div>

                        <!-- Phone Number -->
                        <div class="col-md-6">
                            <label for="phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input id="phone_number" name="phone_number" class="form-control" 
                                   value="{{ old('phone_number', $user->phone_number ?? '') }}" required>
                            <div class="invalid-feedback">Please provide a phone number.</div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input id="password" name="password" type="password" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Please provide a password.</div>
                            <div class="form-text">Minimum 8 characters</div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                            <div class="invalid-feedback">Passwords must match.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex justify-content-end gap-3 mb-4">
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-2"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Save Business
                </button>
            </div>
        </form>
    </div>
@endsection


@section('scripts')

<script>
    // Form validation
    (function () {
        'use strict'
        
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')
    
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
    })()
    
   document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
    
        // Only proceed if both elements exist
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const icon = this.querySelector('i');
                const isPassword = passwordInput.type === 'password';
    
                passwordInput.type = isPassword ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
            });
        } else {
            console.warn('togglePassword or password element not found in the DOM.');
        }
    });

    
    document.addEventListener('DOMContentLoaded', function () {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
    
        if (password && confirmPassword) {
            confirmPassword.addEventListener('input', function () {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords must match");
                } else {
                    confirmPassword.setCustomValidity("");
                }
            });
    
            // Optional: re-validate on password input as well
            password.addEventListener('input', function () {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords must match");
                } else {
                    confirmPassword.setCustomValidity("");
                }
            });
        } else {
            console.warn('Password or confirmation field not found.');
        }
    });

</script>
@endsection
