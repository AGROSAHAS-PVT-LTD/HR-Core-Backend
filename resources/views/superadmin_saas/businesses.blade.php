
@php
    use App\Models\Settings;
    $title = 'Businesses | Super Admin';
    $settings = Settings::first();

@endphp

@extends('layouts/layoutMaster')

@section('title', 'Businesses')

@section('content')
    @include('nav')

    
    <div class="row mb-3 mt-5">
        <div class="col">
            <div class="float-start">
                <h4 class="mt-2">{{$title}}</h4>
            </div>
        </div>
        <div class="col">
            <div class="float-end">
                <a href="{{ route('superadmin.businesses.add') }}" class="btn btn-primary">Add Business</a>
            </div>
        </div>
    </div>
    <div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Country</th>
                        <th>City</th>
                        <!--<th>Created By</th>-->
                        <th>Created At</th> <!-- Added Created At column -->
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($businesses as $business)
                        <tr>
                            <td>{{ $business->name }}</td>
                            <td>{{ $business->email }}</td>
                            <td>{{ $business->contact }}</td>
                            <td>{{ $business->country }}</td>
                            <td>{{ $business->city }}</td>
                            <td>{{ $business->created_at->format('Y-m-d H:i:s') }}</td> <!-- Display Created At -->
                             <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="statusSwitch{{ $business->id }}"
                                           onchange="toggleBusinessStatus({{ $business->id }}, this.checked)"
                                           {{ $business->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusSwitch{{ $business->id }}">
                                        {{ $business->is_active ? 'Active' : 'Inactive' }}
                                    </label>
                                </div>
                            </td>
                            <!--<td>-->
                            <!--    <span class="badge {{ $business->is_active ? 'bg-success' : 'bg-danger' }}">-->
                            <!--        {{ $business->is_active ? 'Active' : 'Inactive' }}-->
                            <!--    </span>-->
                            <!--</td>-->
                            <td>
                                <div class="d-flex flex-column align-items-start">
                                    <a href="{{ route('superadmin.businesses.show', $business->id) }}" class="btn btn-outline-primary btn-sm mb-2">
                                        Manage
                                    </a>
                                    <!-- Add Subscription Button -->
                                    <button class="btn btn-outline-primary btn-sm mb-2" onclick="openSubscriptionModal({{ $business->id }})">
                                        Add Subscription
                                    </button>
                            
                                    <!-- Delete Button -->
                                    <form action="{{ route('superadmin.businesses.delete', $business->id) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this business? All associated data (subscriptions, users, etc.) will be permanently deleted.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm mt-2">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>


                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination links --}}
        <div class="mt-3">
            {{ $businesses->links() }} {{-- This will render the pagination links --}}
        </div>
    </div>
</div>
<!-- Add Subscription Modal -->

 <form id="subscriptionForm" method="POST">
      @csrf
    <div class="modal fade" id="subscriptionModal" tabindex="-1" aria-labelledby="subscriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subscriptionModalLabel">Add Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Select Package -->
                        <label for="package_id">Select Package</label>
                        <select id="package_id" name="package_id" class="form-select" required>
                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }}</option>
                            @endforeach
                    </select>
    
                        <!-- Select Payment Method -->
                        <label for="payment_method" class="mt-3">Payment Method</label>
                        <select id="payment_method" name="payment_method" class="form-select" required>
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method }}">{{ ucfirst($method) }}</option>
                            @endforeach
                    </select>
    
                        <!-- Transaction ID -->
                        <label for="transaction_id" class="mt-3">Transaction ID</label>
                        <input type="text" id="transaction_id" name="transaction_id" class="form-control" required>
                    
                    
                  
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Subscription</button>
                        </div>
            </div>
        </div>
    </div>
  </form>

@endsection

<!--@section('scripts')-->
<script>
    const csrfToken = '{{ csrf_token() }}'; // Laravel CSRF token
    
    function openSubscriptionModalV2(businessId) {
        // Show the modal
        const subscriptionModal = new bootstrap.Modal(document.getElementById('subscriptionModal'));
        subscriptionModal.show();
    
        // Set up a form submission handler
        document.getElementById('subscriptionForm').onsubmit = function(event) {
            event.preventDefault(); // Prevent default form submission
            
            // Gather form data
            const formData = new FormData(this);
            formData.append('businessId', businessId);
            // Send data using fetch API
            fetch(`/public/superadmin/businesses/add-subscription`, {
                method: 'POST', // Or 'PUT' if preferred
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Subscription added successfully!");
                    subscriptionModal.hide();
                    // Optionally reload the page or update the UI
                } else {
                    alert("There was an error: " + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        };
    }

    function openSubscriptionModalV2(businessId) {
        // Show the modal
        const subscriptionModal = new bootstrap.Modal(document.getElementById('subscriptionModal'));
        subscriptionModal.show();
        // Set up a form submission handler
        document.getElementById('subscriptionForm').onsubmit = function(event) {
            event.preventDefault(); // Prevent default form submission
    
            // Gather form data
            const formData = new FormData(this);
            formData.append('businessId', businessId);
            
            // Send data using fetch API
            fetch(`/public/superadmin/add-subscription`, {
                method: 'POST',
                headers: {
                     'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Subscription added successfully!");
                    subscriptionModal.hide();
                    // Optionally reload the page or update the UI
                } else {
                    alert("There was an error: " + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        };
    }
    
    function openSubscriptionModal(businessId) {
        document.getElementById('subscriptionForm').action = `/public/superadmin/businesses-add-subscription/${businessId}`;
        new bootstrap.Modal(document.getElementById('subscriptionModal')).show();
    }

    function toggleBusinessStatus(businessId, isActive) {

        fetch(`/public/superadmin/businesses/toggle-status/${businessId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ is_active: isActive })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update label text based on new status
                const label = document.querySelector(`#statusSwitch${businessId} + label`);
                label.textContent = isActive ? 'Active' : 'Inactive';
            } else {
                alert('Failed to update status');
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
        });
    }
    
    
</script>
<!--@endsection-->
