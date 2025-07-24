@php
  $configData = Helper::appClasses();
  $title = 'Subscription Details';

@endphp

@extends('layouts/layoutMaster')

@section('title', 'Subscription Details - Apps')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    'resources/assets/vendor/libs/animate-css/animate.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
    ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
    ])
@endsection

@section('page-script')
  @vite([
    'resources/assets/js/app/role-index.js',
    ])
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0"><i class="fas fa-store-alt me-2"></i>{{ $title }}</h5>
        <div class="d-flex">
            @if (!$currentSubscription->isInTrialPeriod())
                @if(!$business->is_active || !$currentSubscription || !$currentSubscription->isActive())
                    <button class="btn btn-sm btn-outline-warning me-2" data-bs-toggle="tooltip" title="Business Status">
                        <i class="fas fa-exclamation-circle me-1"></i> Attention Needed
                    </button>
                @endif
            @endif


            
        </div>
    </div>
    @if (!$currentSubscription->isInTrialPeriod())
    <!-- Alert Section -->
    @if(!$business->is_active || !$currentSubscription || !$currentSubscription->isActive())
        <div class="alert alert-warning alert-dismissible fade show shadow-sm">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-2">Important Reminder</h5>
                    <ul class="mb-0 ps-3">
                        @if(!$business->is_active)
                            <li class="mb-1">
                                The business <strong>{{ $business->name }}</strong> is currently <span class="badge bg-danger">Inactive</span>. 
                                Please review the status to ensure full functionality.
                            </li>
                        @endif
                        @if(!$currentSubscription || !$currentSubscription->isActive())
                            <li class="mb-1">
                                No active subscription found. Service interruptions may occur without renewal.
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @endif

    <!-- Business Overview Cards -->
    <div class="row g-4 mb-4">
        <!-- Business Card -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-secondary mb-2 bg-opacity-10">
                    <h5 class="card-title mb-0"><i class="fas fa-building me-2"></i>Business Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <h4 class="mb-3 text-primary">{{ $business->name }}</h4>
                        <div class="mb-2">
                            <span class="text-muted small">Email:</span>
                            <p class="mb-0">{{ $business->email }}</p>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small">Contact:</span>
                            <p class="mb-0">{{ $business->contact }}</p>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted small">Status:</span>
                            <span class="badge bg-{{ $business->is_active ? 'success' : 'danger' }}">
                                {{ $business->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="mt-auto pt-2">
                            <small class="text-muted">Created: {{ $business->created_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Card -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-secondary mb-2 bg-opacity-10">
                    <h5 class="card-title mb-0"><i class="fas fa-credit-card me-2"></i>Subscription</h5>
                </div>
                <div class="card-body">
                    @if($currentSubscription)
                        <div class="d-flex flex-column h-100">
                            <div class="mb-2">
                                <h6 class="text-info mb-1">Current Plan</h6>
                                <h4 class="mb-1">{{ $currentSubscription->package->name ?? 'N/A' }}</h4>
                                <h5 class="text-success">UGX {{ $currentSubscription->formatted_package_price }}</h5>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Type:</span>
                                    <span class="badge bg-{{ $currentSubscription->isInTrialPeriod() ? 'warning ' : 'primary' }}">
                                        {{ $currentSubscription->isInTrialPeriod() ? 'Trial' : 'Paid' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Status:</span>
                                    <span class="badge bg-{{ $currentSubscription->isActive() || $currentSubscription->isInTrialPeriod() ? 'success' : 'danger' }}">
                                        {{ $currentSubscription->isActive() || $currentSubscription->isInTrialPeriod() ? 'Active' : 'Expired' }}
                                    </span>
                                </div>


                                <div class="d-flex justify-content-between">
                                    <span>Remaining:</span>
                                    <span>{{ ($currentSubscription->remaining_days) }} days</span>

                                </div>
                            </div>
                            
                            <div class="progress mt-auto" style="height: 8px;">
                                @php
                                    $percentage = min(100, max(0, (($currentSubscription->remaining_days / $currentSubscription->subscription_days) * 100)));
                                @endphp
                                <div class="progress-bar bg-{{ $percentage > 20 ? 'success' : 'warning' }}" 
                                     role="progressbar" 
                                     style="width: {{ $percentage }}%" 
                                     aria-valuenow="{{ $percentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                            <h5>No Active Subscription</h5>
                            <p class="text-muted">Please select a plan to continue service</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Owner Card -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-secondary mb-2 bg-opacity-10">
                    <h5 class="card-title mb-0"><i class="fas fa-user-tie me-2"></i>Owner Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column h-100">
                        <div class="text-center mb-3">
                            <div class="avatar avatar-xl bg-light-primary rounded-circle mb-2">
                                <span class="avatar-content">
                                    {{ substr($business->owner->first_name ?? '', 0, 1) }}{{ substr($business->owner->last_name ?? '', 0, 1) }}
                                </span>
                            </div>
                            <h4 class="mb-0">{{ $business->owner->first_name ?? '' }} {{ $business->owner->last_name ?? '' }}</h4>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-envelope me-2 text-muted"></i>
                                <span>{{ $business->owner->email ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                <span>{{ $business->owner->phone_number ?? 'N/A' }}</span>
                            </div>
                            <div class="d-flex align-items-start">
                                <i class="fas fa-map-marker-alt me-2 text-muted mt-1"></i>
                                <span>{{ $business->owner->address ?? 'Address not specified' }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-auto pt-2 text-center">
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-envelope me-1"></i> Contact Owner
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription History Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Subscription History</h5>
                <div>
                    <!-- <button class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="openPaymentModal({{ $currentSubscription->id ?? '0' }})">
                        <i class="fas fa-plus me-1"></i> New Subscription
                    </button> -->
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="120">Actions</th>
                            <th>Package</th>
                            <th>Period</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($business->subscriptions as $subscription)
                            <tr>
                                <td>
                                    @if($subscription->status !== 'approved')
                                        <button class="btn btn-sm btn-outline-primary" onclick="openPaymentModal({{ $subscription->id }})">
                                            <i class="fas fa-credit-card me-1"></i> Pay
                                        </button>
                                    @else
                                        <span class="badge bg-success">Paid</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $subscription->package->name }}</strong>
                                    <div class="text-muted small">{{ $subscription->isInTrialPeriod() ? 'Trial' : 'Paid' }}</div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">Start: {{ $subscription->start_date }}</small>
                                        <small class="text-muted">End: {{ $subscription->end_date }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="d-block">{{ $subscription->paid_via ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $subscription->payment_transaction_id ?? '' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge 
                                        {{ $subscription->status == 'approved' ? 'bg-success' : 
                                           ($subscription->status == 'waiting' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $subscription->created_at?->format('M d, Y') ?? 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Complete Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm" method="POST" action="{{ route('update.subscription') }}">
                    @csrf
                    <input type="hidden" id="subscriptionId" name="subscription_id">
                    
                    <div class="mb-4">
                        <label class="form-label">Select Payment Method</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary text-start d-flex align-items-center" 
                                    onclick="selectPaymentMethod('flutterwave')">
                                <i class="fab fa-cc-visa me-3 fa-2x"></i>
                                <div>
                                    <h6 class="mb-0">Pay with Flutterwave</h6>
                                    <small class="text-muted">Credit/Debit Card, Mobile Money</small>
                                </div>
                            </button>
                            
                            @if($settings->enable_offline_payment)
                            <button type="button" class="btn btn-outline-secondary text-start d-flex align-items-center" 
                                    onclick="selectPaymentMethod('offline')">
                                <i class="fas fa-university me-3 fa-2x"></i>
                                <div>
                                    <h6 class="mb-0">Bank Transfer</h6>
                                    <small class="text-muted">Manual payment processing</small>
                                </div>
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- Flutterwave Payment -->
                    <div id="flutterwavePayment" class="payment-section d-none">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> You will be redirected to Flutterwave's secure payment page.
                        </div>
                        <button type="button" class="btn btn-primary w-100 py-2" onclick="payWithFlutterwave()">
                            <i class="fas fa-lock me-2"></i> Proceed to Payment
                        </button>
                    </div>

                    <!-- Offline Payment -->
                    <div id="offlinePayment" class="payment-section d-none">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle me-2"></i> Please follow these instructions carefully.
                        </div>
                        <div class="mb-3">
                            <h6 class="mb-2">Bank Transfer Details</h6>
                            <div class="card bg-light p-3">
                                {!! nl2br(e($settings->offline_payment_details)) !!}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">Transaction Reference</label>
                            <input type="text" id="transaction_id" name="transaction_id" class="form-control" 
                                   placeholder="Enter bank transaction ID" required>
                            <small class="text-muted">Provide the reference number from your bank transfer</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-paper-plane me-2"></i> Submit Payment Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-purple {
        background-color: #6f42c1;
    }
    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-xl {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    .payment-section {
        transition: all 0.3s ease;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
</style>

@endsection



@section('scripts')
<script src="https://checkout.flutterwave.com/v3.js"></script>
<script>
    function openPaymentModal(subscriptionId) {
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        document.getElementById('subscriptionId').value = subscriptionId;
        
        // Reset all payment sections
        document.querySelectorAll('.payment-section').forEach(el => {
            el.classList.add('d-none');
        });
        
        modal.show();
    }

    function selectPaymentMethod(method) {
        // Hide all payment sections
        document.querySelectorAll('.payment-section').forEach(el => {
            el.classList.add('d-none');
        });
        
        // Show selected method
        document.getElementById(method + 'Payment').classList.remove('d-none');
    }

    function payWithFlutterwave() {
        const btn = document.querySelector('#flutterwavePayment button');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
        
        FlutterwaveCheckout({
            public_key: "{{ $settings->flutterwave_public_key }}",
            tx_ref: 'FLW-'+Math.random().toString(36).substring(2, 15),
            amount: {{ $currentSubscription->package->price ?? 0 }},
            currency: "{{ $settings->currency }}",
            payment_options: "card, mobilemoneyuganda, ussd",
            redirect_url: "{{ route('home.postFlutterwavePaymentCallback') }}",
            customer: {
                email: "{{ $business->owner->email }}",
                phone_number: "{{ $business->owner->phone_number }}",
                name: "{{ $business->owner->first_name }} {{ $business->owner->last_name }}",
            },
            customizations: {
                title: "{{ $business->name }} Subscription",
                description: "Payment for {{ $currentSubscription->package->name ?? '' }}",
                logo: "{{ asset('images/logo.png') }}",
            },
            callback: function(response) {
                // Handle successful payment
                console.log(response);
            },
            onclose: function() {
                // Reset button if payment not completed
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }
</script>
@endsection


