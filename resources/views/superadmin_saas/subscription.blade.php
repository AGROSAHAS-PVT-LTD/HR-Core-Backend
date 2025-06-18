@php
    use App\Models\Settings;
    $title = 'Subscriptions | Super Admin';
    $settings = Settings::first();
@endphp

@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    @include('nav') 

    <!-- <div class="card">
    <div class="card-datatable table-responsive">
      <table class="datatables-users table border-top">
        <thead> -->

    <div class="row mb-3 mt-5">
        <div class="col">
            <div class="float-start">
                <h4 class="mt-2">{{ $title }}</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="card-datatable table-responsive">
                <table id="datatable" class="datatables-users table border-top">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Package Details</th>
                            <!--<th>Original Price</th>-->
                            <th>Package Price</th>
                             <th>Orignal Transaction Id</th>
                             <th>Paid Via</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->business->name ?? 'N/A' }}</td>
                                <td>{{ $subscription->package->name ?? 'N/A' }}</td>
                                <!--<td>{{ $subscription->original_price }}</td>-->
                                <td>{{ $subscription->package_price }}</td>
                                  <td>{{ $subscription->payment_transaction_id ?? 'N/A' }}</td>
                                 <td>{{ $subscription->paid_via ?? 'N/A' }}</td>
                                <td>{{ $subscription->start_date }}</td>
                                <td>{{ $subscription->end_date }}</td>
                                <td>
                                    <span class="badge 
                                        {{ $subscription->status == 'approved' ? 'bg-success' : 
                                           ($subscription->status == 'waiting' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </td>

                                <td>
                                <div class="d-flex flex-column align-items-start">
                                     <a href="#" class="btn btn-outline-primary btn-sm mb-2" data-bs-toggle="modal" 
                                     onclick="openStatusModal({{ $subscription->id }}, '{{ $subscription->status }}', '{{ $subscription->payment_transaction_id }}')" >
                                        Status
                                    </a>
                                    <a href="#" 
                                    class="btn btn-outline-primary btn-sm mb-2" data-bs-toggle="modal" 
                                    onclick="openEditModal({{ $subscription->id }}, '{{ $subscription->start_date }}', '{{ $subscription->end_date }}', '{{ $subscription->trial_end_date ? $subscription->trial_end_date : '' }}')">
                                        
                                        Edit
                                    </a>
                                   
                                
                                </div>
                            </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination links --}}
            <div class="mt-3">
                {{ $subscriptions->links() }}
            </div>
        </div>
        
        
    </div>
    <!-- Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="statusForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalLabel">Update Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                           <select class="form-select" id="status" name="status">
                                <option value="approved" >Approved</option>
                                <option value="waiting">Waiting</option>
                                <option value="declined">Declined</option>
                            </select>

                        </div>
                        <div class="mb-3">
                            <label for="payment_transaction_id" class="form-label">Payment Transaction ID</label>
                            <input type="text" class="form-control" id="payment_transaction_id" name="payment_transaction_id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST" >
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Dates</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                        <div class="mb-3">
                            <label for="trial_end_date" class="form-label">Trial End Date</label>
                            <input type="date" class="form-control" id="trial_end_date" name="trial_end_date">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


@section('scripts')
<script>
    function openStatusModal(id, status, paymentTransactionId) {
        // Set the form action URL
        document.getElementById('statusForm').action =   `/public/superadmin/subscriptions/update-status/${id}`;
        
        // Set the current values in the modal
        document.getElementById('status').value = status;
        document.getElementById('payment_transaction_id').value = paymentTransactionId || '';
        // Show the modal
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    }

    function openEditModal(id, startDate, endDate, trialEndDate) {
        // Set the form action URL
        document.getElementById('editForm').action = `/public/superadmin/subscriptions/update-dates/${id}`;
        // Set the current values in the modal
        document.getElementById('start_date').value = startDate;
        document.getElementById('end_date').value = endDate;
        document.getElementById('trial_end_date').value = trialEndDate || '';
        // Show the modal
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }
</script>
@endsection