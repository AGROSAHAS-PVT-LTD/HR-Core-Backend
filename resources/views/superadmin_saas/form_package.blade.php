@php
    use App\Models\Settings;
    $title = $package ? 'Edit Package | Super Admin' : 'Create Package | Super Admin';
    $settings = Settings::first();
@endphp

@extends('layouts/layoutMaster')

@section('title', $title)

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
    <!-- <div class="row mb-3 mt-5">
        <div class="col">
            <div class="float-start">
                <h4 class="mt-2">{{$title}}</h4>
            </div>
        </div>
    </div> -->
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
                        <div class="form-group row">
                            <div class="form-group col-md-4 mb-3">
                                <label for="name" class="control-label">Package Name *</label>
                                <input id="name" name="name" class="form-control" value="{{ old('name', $package->name ?? '') }}" required/>
                                <span class="text-danger">{{ $errors->first('name', ':message') }}</span>
                            </div>
                             <div class="form-group col-md-4 mb-3">
                                <label for="is_one_time" class="control-label">One Time Package</label>
                                <select id="is_one_time" name="is_one_time" class="form-select" required>
                                    <option value="0" {{ old('is_one_time', $package->is_one_time ?? '') == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('is_one_time', $package->is_one_time ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                                <span class="text-danger">{{ $errors->first('is_one_time', ':message') }}</span>
                            </div>
                            <div class="form-group col-md-4 mb-3">
                                <label for="mark_package_as_popular" class="control-label">Mark as Popular *</label>
                                <select id="mark_package_as_popular" name="mark_package_as_popular" class="form-select">
                                    <option value="1" {{ old('mark_package_as_popular', $package->mark_package_as_popular ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('mark_package_as_popular', $package->mark_package_as_popular ?? '') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                                <span class="text-danger">{{ $errors->first('mark_package_as_popular', ':message') }}</span>
                            </div>
                        </div>

        
                        <!-- Pricing Interval and Interval Count -->
                        <div class="form-group row">
                        
                            <div class="form-group col-md-4 mb-3">
                                <label for="interval" class="control-label">Price Interval *</label>
                                <select id="interval" name="interval" class="form-select" required>
                                    <option value="days" {{ old('interval', $package->interval ?? '') == 'days' ? 'selected' : '' }}>Days</option>
                                    <option value="months" {{ old('interval', $package->interval ?? '') == 'months' ? 'selected' : '' }}>Months</option>
                                    <option value="years" {{ old('interval', $package->interval ?? '') == 'years' ? 'selected' : '' }}>Years</option>
                                </select>
                                <span class="text-danger">{{ $errors->first('interval', ':message') }}</span>
                            </div>
                            <div class="form-group col-md-4 mb-3">
                                <label for="interval_count" class="control-label">Interval Count *</label>
                                <input id="interval_count" name="interval_count" type="number" class="form-control" value="{{ old('interval_count', $package->interval_count ?? '') }}"/>
                                <span class="text-danger">{{ $errors->first('interval_count', ':message') }}</span>
                            </div>
                            <div class="form-group col-md-4 mb-3">
                                <label for="price" class="control-label">Price *</label>
                                <input id="price" name="price" type="number" class="form-control" value="{{ old('price', $package->price ?? '') }}" required/>
                                <span class="text-danger">{{ $errors->first('price', ':message') }}</span>
                            </div>
                        </div>

                        <!-- Trial Days and User Count -->
                        <div class="form-group row">
                            
                            <div class="form-group col-md-4 mb-3">
                                <label for="trial_days" class="control-label">Trial Days </label>
                                <input id="trial_days" name="trial_days" type="number" class="form-control" value="{{ old('trial_days', $package->trial_days ?? '') }}"/>
                                <span class="text-danger">{{ $errors->first('trial_days', ':message') }}</span>
                            </div>
                            
                            <div class="form-group col-md-4 mb-3">
                                <label for="user_count" class="control-label">User Count* </label>
                                <input id="user_count" name="user_count" type="number" class="form-control" value="{{ old('user_count', $package->user_count ?? '') }}"/>
                                <span class="text-danger">{{ $errors->first('user_count', ':message') }}</span>
                            </div>
                            <div class="form-group col-md-4 mb-3">
                                <label for="status" class="control-label">Status *</label>
                                <select id="status" name="is_active" class="form-select" required>
                                    <option value="1" {{ old('is_active', $package->is_active ?? '') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $package->is_active ?? '') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <span class="text-danger">{{ $errors->first('is_active', ':message') }}</span>
                            </div>
                        </div>
                        <!--Package Description and Features -->
                        <div class="form-group row">
                            <div class="form-group col-md-12 mb-3">
                                <label for="description" class="control-label">Description *</label>
                                <textarea rows="8"  id="description" name="description" class="form-control" required>{{ old('description', $package->description ?? '') }}</textarea>
                                <span class="text-danger">{{ $errors->first('description', ':message') }}</span>
                            </div>
                            
                        </div>

                    </div>
                </div>

                <div class="form-group mt-2">
                    <button type="submit" class="btn btn-primary">{{ $package ? 'Update Package' : 'Create Package' }}</button>
                </div>
            </div>
        </div>
    </form>

@endsection

@section('styles')
    <style>
        .bg-light {
            background-color: #fff !important;
        }
        .navbar-nav .show>.nav-link, .navbar-nav .nav-link.active {
            color: blue;
        }
        .nav-item-sa {
            padding: 10px;
            font-size: 16px;
        }
    </style>
@endsection
