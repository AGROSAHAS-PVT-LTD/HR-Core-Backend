@php
    use App\Models\Settings;
    $title = 'Packages | Super Admin';
    $settings = Settings::first();
    
    // Helper function to format prices with 2 decimal places
    function formatPrice($amount) {
        return number_format((float)$amount, 1, '.', ',');
    }
@endphp

@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    @include('nav')    

    <div class="d-flex justify-content-between align-items-center mb-4 py-3">
        <h4 class="mb-0">{{ $title }}</h4>
        <a href="{{ route('superadmin.packages.add') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Package
        </a>
    </div>

    <div class="row">
        @foreach($packages as $package)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 package-card {{ $package->mark_package_as_popular ? 'border-primary border-2' : '' }}">
                @if($package->mark_package_as_popular)
                <div class="ribbon ribbon-top-right">
                    <span class="bg-primary">POPULAR</span>
                </div>
                @endif
                
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $package->name }}</h5>
                    <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $package->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                <div class="card-body">
                    <!-- Pricing Section -->
                    <div class="package-pricing mb-3">
                        @if($package->has_yearly_plan && $package->yearly_price)
                            <div class="d-flex justify-content-between align-items-end mb-2">
                                <div>
                                    <h3 class="mb-0 text-primary">{{ formatPrice($package->price) }}</h3>
                                    <small class="text-muted">
                                        per month ({{ $settings->currency_symbol ?? '$' }})
                                    </small>
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0">{{ formatPrice($package->yearly_price) }}</h4>
                                    <small class="text-muted">
                                        per year  ({{ $settings->currency_symbol ?? '$' }}) (save {{ calculateYearlyDiscount($package->price, $package->yearly_price) }}%)
                                    </small>
                                </div>
                            </div>
                        @else
                            <div class="package-price text-center">
                                <h3 class="mb-0">{{ $settings->currency_symbol ?? '$' }}{{ formatPrice($package->price) }}</h3>
                                <small class="text-muted">
                                    per 
                                    @if($package->interval_count > 1)
                                        {{ $package->interval_count }} {{ \Illuminate\Support\Str::plural($package->interval, $package->interval_count) }}
                                    @else
                                        {{ $package->interval }}
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Features Section -->
                    <div class="package-features mb-3">
                        <h6 class="mb-2">Features:</h6>
                        @php
                            $features = json_decode($package->features, true);
                            $featureCount = is_array($features) ? count(array_filter($features)) : 0;
                        @endphp
                        
                        @if($featureCount > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(array_slice(array_keys(array_filter($features)), 0, 3) as $feature)
                                <span class="badge bg-label-primary">{{ preg_replace('/(?<!^)([A-Z])/', ' $1', $feature) }}</span>
                            @endforeach
                            @if($featureCount > 3)
                                <span class="badge bg-label-info">+{{ $featureCount - 3 }} more</span>
                            @endif
                        </div>
                        @else
                        <span class="text-muted">No features selected</span>
                        @endif
                    </div>
                    
                    <!-- Package Meta Information -->
                    <div class="package-meta">
                        <div class="row small text-muted">
                            <div class="col-6 mb-2">
                                <i class="fas fa-users me-1"></i>
                                {{ $package->user_count }} users
                            </div>
                            <div class="col-6 mb-2">
                                <i class="fas fa-clock me-1"></i>
                                {{ $package->trial_days ?? 0 }} trial days
                            </div>
                            <div class="col-6">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ ucfirst($package->interval) }} billing
                            </div>
                            <div class="col-6">
                                <i class="fas fa-calendar-alt me-1"></i>
                                {{ $package->interval_count }} {{ \Illuminate\Support\Str::plural($package->interval, $package->interval_count) }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Card Footer with Actions -->
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('superadmin.packages.edit', $package->id) }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        
                        <form action="{{ route('superadmin.packages.delete', $package->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('Are you sure you want to delete this Package?')">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $packages->links() }}
    </div>
@endsection

@section('page-style')
<style>
    .package-card {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .package-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .package-card .card-header {
        margin-top: 10px;
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .package-pricing {
        background-color: rgba(0, 0, 0, 0.02);
        padding: 0.75rem;
        border-radius: 0.375rem;
    }
    
    .package-price {
        text-align: center;
    }
    
    .ribbon {
        width: 100px;
        height: 88px;
        overflow: hidden;
        position: absolute;
    }
    
    .ribbon::before,
    .ribbon::after {
        position: absolute;
        z-index: -1;
        content: '';
        display: block;
        border: 5px solid #2980b9;
    }
    
    .ribbon span {
        position: absolute;
        display: block;
        width: 120px;
        padding: 5px 0;
        background-color: #3498db;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        color: #fff;
        font-size: 0.7rem;
        text-align: center;
        right: -5px;
        top: 0px;
        transform: rotate(360deg);
    }
    
    @media (max-width: 767.98px) {
        .package-card {
            margin-bottom: 1.5rem;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add any interactive functionality here
    });
</script>
@endsection

@php
    // Helper function to calculate yearly discount percentage
    function calculateYearlyDiscount($monthlyPrice, $yearlyPrice) {
        if (!$monthlyPrice || !$yearlyPrice) return 0;
        
        $monthlyTotal = $monthlyPrice * 12;
        $discount = (($monthlyTotal - $yearlyPrice) / $monthlyTotal) * 100;
        return round($discount, 0);
    }
@endphp