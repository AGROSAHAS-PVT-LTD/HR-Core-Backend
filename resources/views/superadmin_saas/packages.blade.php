@php
    use App\Models\Settings;
    $title = 'Packages | Super Admin';
    $settings = Settings::first();
@endphp

@extends('layouts/layoutMaster')

@section('title', $title)

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
                <a href="{{ route('superadmin.packages.add') }}" class="btn btn-primary">Add Package</a>
            </div>
        </div>
    </div>
    <div class="card">
    <div class="card-body">
        <div class="card-datatable table-responsive">
            <table id="datatable" class="datatables-users table border-top">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <!--<th>Duration</th>-->
                        <!--<th>Features</th>-->
                        <th>Trial Days</th>
                        <th>Interval</th>
                        <th>Interval Count</th>
                        <th>User Count</th>
                        <th>Mark as Popular</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $package)
                        <tr>
                            <td>{{$package->name}}</td>
                            <td>{{$package->description}}</td>
                            <td>{{$package->price}}</td>
                            <!--<td>{{$package->duration}}</td>-->
                            <!--<td>{{ $package->features }}</td>-->
                            <td>{{ $package->trial_days }}</td>
                            <td>{{ ucfirst($package->interval) }}</td>
                            <td>{{$package->interval_count}}</td>
                            <td>{{$package->user_count}}</td>
                            <td>
                                <span class="badge {{ $package->mark_package_as_popular ? 'bg-success' : 'bg-warning' }}">
                                    {{ $package->mark_package_as_popular ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $package->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                             <td>
                                <a href="{{ route('superadmin.packages.edit', $package->id) }}" class="btn btn-primary btn-sm mb-2">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <form action="{{ route('superadmin.packages.delete', $package->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this Package?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination links --}}
        <div class="mt-3">
            {{ $packages->links() }} {{-- This will render the pagination links --}}
        </div>
    </div>
</div>

@endsection


@section('scripts')
    <!-- Add any page-specific JavaScript here -->
@endsection
