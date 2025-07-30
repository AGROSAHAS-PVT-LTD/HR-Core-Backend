@php
    use App\Models\Settings;
    $title = $business->name;
    $settings = Settings::first();
@endphp

@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    @include('nav')

    <div class="container-fluid px-4 py-3">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 text-primary">{{ $business->name }}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.businesses') }}">Businesses</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Details</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('superadmin.businesses.edit', $business->id) }}" class="btn btn-primary me-2">
                    <i class="fas fa-edit me-2"></i>Edit Business
                </a>
                <a href="{{ route('superadmin.businesses') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>

        <!-- Status Alerts -->
        @if(!$business->is_active || !$currentSubscription || !$currentSubscription->isActive())
            <div class="alert alert-warning alert-dismissible fade show mb-4">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Important Reminder</h5>
                <ul class="mb-0">
                    @if(!$business->is_active)
                        <li>
                            The business <strong>{{ $business->name }}</strong> is currently marked as <span class="badge bg-danger">Inactive</span>. 
                            Please activate it to ensure full functionality.
                        </li>
                    @endif
                    @if(!$currentSubscription || !$currentSubscription->isActive())
                        <li>
                            The business does not have an active subscription or the current subscription has expired. 
                            <span class="badge bg-danger">Service Interruption Risk</span>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Business Overview Cards -->
        <div class="row g-4 mb-4">
            <!-- Business Info Card -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-info mb-2 bg-opacity-10">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i> Business Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            @if($business->logo)
                                <img src="{{ asset('storage/'.$business->logo) }}" alt="Business Logo" class="rounded-circle me-3" width="60" height="60">
                            @endif
                            <div>
                                <h4 class="mb-0">{{ $business->name }}</h4>
                                <span class="badge bg-{{ $business->is_active ? 'success' : 'danger' }}">
                                    {{ $business->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span><i class="fas fa-envelope me-2 text-muted"></i>Email</span>
                                <span>{{ $business->email ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span><i class="fas fa-phone me-2 text-muted"></i>Contact</span>
                                <span>{{ $business->contact ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span><i class="fas fa-globe me-2 text-muted"></i>Website</span>
                                <span>{{ $business->website ?: 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                <span><i class="fas fa-calendar me-2 text-muted"></i>Created</span>
                                <span>{{ $business->created_at->format('M d, Y') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Subscription Card -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-info mb-2 bg-opacity-10">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i> Subscription</h5>
                    </div>
                    <div class="card-body">
                        @if($currentSubscription)
                            <div class="mb-3">
                                <h4 class="mb-1">{{ $currentSubscription->package->name ?? 'N/A' }}</h4>
                                <span class="badge bg-{{ $currentSubscription->isActive() ? 'success' : 'danger' }}">
                                    {{ $currentSubscription->isActive() ? 'Active' : 'Expired' }}
                                </span>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                    <span><i class="fas fa-money-bill-wave me-2 text-muted"></i>Price</span>
                                    <span>{{ $settings->currency_symbol }}{{ number_format($currentSubscription->package->price, 2) }}</span>
                                </li>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                    <span><i class="fas fa-calendar-check me-2 text-muted"></i>Start Date</span>
                                    <span>{{ $currentSubscription->start_date }}</span>
                                </li>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                    <span><i class="fas fa-calendar-times me-2 text-muted"></i>End Date</span>
                                    <span>{{ $currentSubscription->end_date }}</span>
                                </li>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                    <span><i class="fas fa-clock me-2 text-muted"></i>Remaining</span>
                                    <span>{{ $currentSubscription->remaining_days }} days</span>
                                </li>
                            </ul>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                                <h5>No Active Subscription</h5>
                                <p class="text-muted">This business doesn't have an active subscription.</p>
                                
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Owner Card -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-info mb-2 bg-opacity-10">
                        <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i> Owner</h5>
                    </div>
                    <div class="card-body">
                        @if($business->owner)
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-lg me-3">
                                    <span class="avatar-initial rounded-circle bg-primary text-white">
                                        {{ substr($business->owner->first_name, 0, 1) }}{{ substr($business->owner->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="mb-0">{{ $business->owner->first_name }} {{ $business->owner->last_name }}</h4>
                                    <small class="text-muted">Owner</small>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                    <span><i class="fas fa-envelope me-2 text-muted"></i>Email</span>
                                    <span>{{ $business->owner->email }}</span>
                                </li>
                                <li class="list-group-item px-0 py-2 d-flex justify-content-between">
                                    <span><i class="fas fa-phone me-2 text-muted"></i>Phone</span>
                                    <span>{{ $business->owner->phone_number ?? 'N/A' }}</span>
                                </li>
                                <li class="listcrumb-item px-0 py-2 d-flex justify-content-between">
                                    <span><i class="fas fa-map-marker-alt me-2 text-muted"></i>Address</span>
                                    <span>{{ $business->owner->address ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item px-0 py-2">
                                    <a href="mailto:{{ $business->owner->email }}" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="fas fa-envelope me-2"></i>Contact Owner
                                    </a>
                                </li>
                            </ul>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-user-slash fa-3x text-secondary mb-3"></i>
                                <h5>No Owner Assigned</h5>
                                <p class="text-muted">This business doesn't have an owner assigned.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription History -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info mb-2 bg-opacity-10 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i> Subscription History</h5>
              
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Package</th>
                                <th>Period</th>
                                <th>Payment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($business->subscriptions as $subscription)
                                <tr>
                                    <td>
                                        <strong>{{ $subscription->package->name }}</strong>
                                        <div class="small text-muted">{{ $settings->currency_symbol }}{{ number_format($subscription->package->price, 2) }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $subscription->start_date }}</div>
                                        <div class="small text-muted">to {{ $subscription->end_date }}</div>
                                        @if($subscription->trial_end_date)
                                            <div class="small text-info">Trial until {{ $subscription->trial_end_date}}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $subscription->paid_via ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $subscription->payment_transaction_id ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $subscription->status == 'approved' ? 'success' : ($subscription->status == 'waiting' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </td>
                                  
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No subscription history found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Users Management -->
        <div class="card shadow-sm">
            <div class="card-header bg-info mb-2 bg-opacity-10 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i> Users ({{ $business->users->count() }})</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus me-2"></i>Add User
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($business->users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded-circle bg-primary text-white">
                                                    {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $user->first_name }} {{ $user->last_name }}</h6>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->roles()->first()->name }}</td>
                                    <td>{{ $user->phone_number ?? 'N/A' }}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" 
                                                   id="statusSwitch{{ $user->id }}" 
                                                   onchange="toggleUserStatus({{ $user->id }}, this.checked)"
                                                   {{ $user->status == 'active' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="statusSwitch{{ $user->id }}">
                                                {{ ($user->status) }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" onclick="openPasswordResetModal({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                                                <i class="fas fa-key"></i>
                                            </button>
                                          
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Reset Modal -->
    <div class="modal fade" id="passwordResetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="passwordResetForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reset Password for <span id="userName"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="newPassword" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

  
@endsection

@section('scripts')
@section('scripts')
@section('scripts')
<script>
    function toggleUserStatus(userId, isActive) {
        const newStatus = isActive ? 'active' : 'inactive';
        fetch(`/superadmin/users/toggle-status/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`statusSwitch${userId}`).nextElementSibling.innerHTML = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            } else {
                alert("Failed to update status.");
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function openPasswordResetModal(userId) {
        document.getElementById('passwordResetForm').action = `/superadmin/users/reset-password/${userId}`;
        new bootstrap.Modal(document.getElementById('passwordResetModal')).show();
    }
</script>
@endsection

@endsection

@endsection