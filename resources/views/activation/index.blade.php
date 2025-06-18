@php
  $activationService = app()->make(\App\Services\ActivationService\IActivationService::class);
  $licenseInfo = $activationService->getActivationInfo();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Activation')

@section('content')

  <div class="container mt-5">
    {{-- Activation Information Card --}}
    <div class="card shadow-lg border-0 rounded-4 mb-4" style="background-color: #f8f9fa;">
      <div class="card-body py-4 px-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h4 class="fw-bold text-primary mb-0">Activation Information</h4>
          @if(isset($licenseInfo['data']) && $licenseInfo['data']['status'] == 'Activated')
            <span class="badge bg-success fs-5 px-4 py-2">Activated</span>
          @else
            <span class="badge bg-danger fs-5 px-4 py-2">Not Activated</span>
          @endif
        </div>

        {{-- Product Information Section --}}
        @if(isset($licenseInfo['data']))
          <div class="row mb-4">
            <div class="col-md-6">
              <h4 class="fw-bold text-dark">Product Information</h4>
              <ul class="list-unstyled ps-3">
                <li class="mb-2"><strong>Product Name:</strong> {{ $licenseInfo['data']['product']['name'] }}</li>
                <li class="mb-2"><strong>Version:</strong> {{ $licenseInfo['data']['product']['version'] }}</li>
              </ul>
            </div>

            {{-- License Details Section --}}
            <div class="col-md-6">
              <h4 class="fw-bold text-dark">License Details</h4>
              <ul class="list-unstyled ps-3">
                <li class="mb-2"><strong>Licensed To:</strong> {{ $licenseInfo['data']['licensedTo'] }}</li>
                <li class="mb-2"><strong>Customer ID:</strong> {{ $licenseInfo['data']['customerId'] }}</li>
                <li class="mb-2"><strong>Activation
                    Date:</strong> {{ \Carbon\Carbon::parse($licenseInfo['data']['activationDate'])->format('Y-m-d H:i') }}
                </li>
                <li class="mb-2"><strong>Expiry Date:</strong>
                  @if($licenseInfo['data']['expiryDate'])
                    {{ \Carbon\Carbon::parse($licenseInfo['data']['expiryDate'])->format('Y-m-d H:i') }}
                  @else
                    <span class="text-muted">No Expiry</span>
                  @endif
                </li>
              </ul>
            </div>
          </div>
        @else
          <div class="">
            <div class="text-center text-danger">
              <i class="bx bx-error-circle fs-1"></i>
              <h4>Activation pending</h4>
              <p>Activation information is not available. Please ensure the system is activated.</p>
              <p>You're running an unlicensed copy of {{ config('variables.templateName') }}.</p>
            </div>
            <!-- License key enter and activation button -->
            <form action="{{route('activation.activate')}}" method="POST">
              @csrf
              <div class="row mt-3 align-content-around justify-content-center">
                <div class="col-4 col-md-6">
                  <label for="licenseKey" class="text-start">License Key</label>
                  <input class="form-control mb-3" id="licenseKey" name="licenseKey"
                         placeholder="XXXX-XXXX-XXXX-XXXX-XXXX"/>
                  {{-- Action Button --}}
                  <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn btn-primary px-5">
                      Activate Now
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        @endif
      </div>

    </div>

@endsection
