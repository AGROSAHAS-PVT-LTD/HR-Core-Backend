@php
    use App\Models\Settings;
    $title = $package ? 'Edit Package | Super Admin' : 'Create Package | Super Admin';
    $settings = Settings::first();

    $features = [
        "NONE", "FarmersManangement", "DocumentsManangement",
        "EmployeManangement", "TaskManangement", "CustomFormsManangement",
        "AttendanceManangement", "NoticeManangement", "PaymentCollectionsManangement",
        "LeaveManangement", "ExpenseManangement"
    ];
    
    $selectedFeatures = [];
    if ($package) {
        $selectedFeatures = $package 
            ? collect(json_decode($package->features, true))
                ->filter(fn($value) => $value === true)
                ->keys()
                ->toArray()
            : [];
    }
@endphp

@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
    @include('nav')
    
    <div class="d-flex justify-content-between align-items-center mb-4 py-3">
        <div>
            <h5 class="mb-0 text-primary">{{ $title }}</h5>
        </div>
        <div>
            <a href="{{ route('superadmin.packages') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>
   
    <form action="{{ route($package ? 'superadmin.packages.update' : 'superadmin.packages.store', $package ? $package->id : '') }}" method="post">
        @csrf
        @if ($package)
            @method('PUT')
        @endif
        
        <div class="card shadow">
            <div class="card-body">
                <div class="card-primary">
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- General Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Package Name <span class="text-danger">*</span></label>
                                <input id="name" name="name" class="form-control" 
                                       value="{{ old('name', $package->name ?? '') }}" required>
                                @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="is_one_time" class="form-label">One Time Package</label>
                                <select id="is_one_time" name="is_one_time" class="form-select" required>
                                    <option value="0" {{ old('is_one_time', $package->is_one_time ?? '') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('is_one_time', $package->is_one_time ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('is_one_time')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="mark_package_as_popular" class="form-label">Mark as Popular</label>
                                <select id="mark_package_as_popular" name="mark_package_as_popular" class="form-select">
                                    <option value="1" {{ old('mark_package_as_popular', $package->mark_package_as_popular ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('mark_package_as_popular', $package->mark_package_as_popular ?? '') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('mark_package_as_popular')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <!-- Pricing Information -->
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="interval" class="form-label">Price Interval <span class="text-danger">*</span></label>
                                <select id="interval" name="interval" class="form-select" required>
                                    <option value="days" {{ old('interval', $package->interval ?? '') == 'days' ? 'selected' : '' }}>Days</option>
                                    <option value="months" {{ old('interval', $package->interval ?? '') == 'months' ? 'selected' : '' }}>Months</option>
                                    <option value="years" {{ old('interval', $package->interval ?? '') == 'years' ? 'selected' : '' }}>Years</option>
                                </select>
                                @error('interval')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="interval_count" class="form-label">Interval Count <span class="text-danger">*</span></label>
                                <input id="interval_count" name="interval_count" type="number" class="form-control" 
                                       value="{{ old('interval_count', $package->interval_count ?? '') }}">
                                @error('interval_count')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="price" class="form-label">Monthly Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $settings->currency_symbol ?? '$' }}</span>
                                    <input id="price" name="price" type="number" step="0.1" class="form-control" 
                                           value="{{ old('price', $package->price ?? '') }}" required>
                                </div>
                                @error('price')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="has_yearly_plan" class="form-label">Has Yearly Plan</label>
                                <select id="has_yearly_plan" name="has_yearly_plan" class="form-select">
                                    <option value="0" {{ old('has_yearly_plan', $package->has_yearly_plan ?? 0) == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('has_yearly_plan', $package->has_yearly_plan ?? 0) == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                                @error('has_yearly_plan')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <!-- Yearly Pricing (Conditional) -->
                        <div class="row" id="yearly_price_section" style="{{ old('has_yearly_plan', $package->has_yearly_plan ?? 0) ? '' : 'display: none;' }}">
                            <div class="col-md-6 mb-3">
                                <label for="yearly_price" class="form-label">Yearly Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $settings->currency_symbol ?? '$' }}</span>
                                    <input id="yearly_price" name="yearly_price" type="number" step="0.1" 
                                           class="form-control" 
                                           value="{{ old('yearly_price', $package->yearly_price ?? '') }}">
                                </div>
                                @error('yearly_price')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-text pt-4">
                                    <span id="yearly_savings">
                                        @if(old('has_yearly_plan', $package->has_yearly_plan ?? 0) && old('price', $package->price ?? 0) && old('yearly_price', $package->yearly_price ?? 0))
                                            Savings: {{ calculateYearlyDiscount(old('price', $package->price), old('yearly_price', $package->yearly_price)) }}%
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Settings -->
                        <div class="row">

                            <div class="col-md-3 mb-3">
                                <label for="trial_days" class="form-label">Trial Days</label>
                                <input id="trial_days" name="trial_days" type="number" class="form-control" 
                                       value="{{ old('trial_days', $package->trial_days ?? '') }}">
                                @error('trial_days')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="user_count" class="form-label">User Count <span class="text-danger">*</span></label>
                                <input id="user_count" name="user_count" type="number" class="form-control" 
                                       value="{{ old('user_count', $package->user_count ?? '') }}">
                                @error('user_count')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="status" name="is_active" class="form-select" required>
                                    <option value="1" {{ old('is_active', $package->is_active ?? '') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $package->is_active ?? '') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <!-- Features Section -->
                        <div class="mb-3">
                            <label for="features" class="form-label">Features</label>
                            <div class="d-flex mb-2">
                                <button type="button" id="select-all" class="btn btn-outline-primary btn-sm me-2">
                                    <i class="fas fa-check-circle me-1"></i> Select All
                                </button>
                                <button type="button" id="deselect-all" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-times-circle me-1"></i> Deselect All
                                </button>
                            </div>
                            <select name="features[]" id="features" class="form-select" multiple>
                                @foreach($features as $feature)
                                    <option value="{{ $feature }}"
                                        @if(in_array($feature, old('features', $selectedFeatures)))
                                            selected
                                        @endif>
                                        {{ $feature }}
                                    </option>
                                @endforeach
                            </select>
                            @error('features')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea id="description" name="description" class="form-control" rows="5" required>{{ old('description', $package->description ?? '') }}</textarea>
                            @error('description')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> {{ $package ? 'Update Package' : 'Create Package' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('page-style')
<style>
    .select2-container--default .select2-selection--multiple {
        min-height: 42px;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 0.375rem;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0 8px;
        margin-right: 5px;
        margin-top: 5px;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #6c757d;
        margin-right: 4px;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #dc3545;
    }
    
    .select2-container .select2-selection--multiple .select2-selection__rendered {
        padding-bottom: 5px;
    }
</style>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for features
        $('#features').select2({
            placeholder: 'Select features...',
            allowClear: true,
            width: '100%',
            closeOnSelect: false
        });
        
        // Select All functionality
        $('#select-all').click(function() {
            $('#features option').prop('selected', true);
            $('#features').trigger('change');
        });
        
        // Deselect All functionality
        $('#deselect-all').click(function() {
            $('#features option').prop('selected', false);
            $('#features').trigger('change');
        });
        
        // Toggle yearly price section
        $('#has_yearly_plan').change(function() {
            if ($(this).val() == '1') {
                $('#yearly_price_section').show();
            } else {
                $('#yearly_price_section').hide();
            }
        });
        
        // Calculate savings when prices change
        $('#price, #yearly_price').on('input', function() {
            if ($('#has_yearly_plan').val() == '1') {
                const monthly = parseFloat($('#price').val()) || 0;
                const yearly = parseFloat($('#yearly_price').val()) || 0;
                
                if (monthly > 0 && yearly > 0) {
                    const monthlyTotal = monthly * 12;
                    const discount = ((monthlyTotal - yearly) / monthlyTotal) * 100;
                    $('#yearly_savings').text('Savings: ' + Math.round(discount) + '%');
                } else {
                    $('#yearly_savings').text('');
                }
            }
        });
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