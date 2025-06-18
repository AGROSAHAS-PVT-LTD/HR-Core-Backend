@php
  $title = 'Form Submissions';
@endphp

  <!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
  ])
@endsection

@extends('layouts/layoutMaster')

@section('content')
  <div class="row mb-3">
    <div class="col">
      <h5 class="mt-2">{{ $title }}</h5>
    </div>
    <div class="col-auto">
      <div class="d-flex align-items-center">
        <!-- Employee Filter Dropdown -->
        <div class="col">
          <label for="employeeFilter" class="form-label">Filter by employee</label>
          <select class="form-select me-2" id="employeeFilter" style="min-width: 200px;">
            <option value="">All Employees</option>
            @foreach($users as $employee)
              <option value="{{ $employee->getFullName() }}">
                {{ $employee->first_name }} {{ $employee->last_name }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="card shadow">
      <div class="card-body">
        <table id="datatable" class="table">
          <thead>
          <tr>
            <th>ID</th>
            <th>Form</th>
            <th>Employee</th>
            <th>Status</th>
            <th>Submitted On</th>
            <th>Action</th>
          </tr>
          </thead>
          <tbody>
          @foreach ($formEntries as $entry)
            <tr>
              <td>{{ $entry->id }}</td>
              <td>{{ $entry->form->name }}</td>
              <td>{{ $entry->user->getFullName() }}</td>
              <td>{{ ucfirst($entry->status) }}</td>
              <td>{{ $entry->created_at->format(Constants::DateTimeFormat) }}</td>
              <td>
                <a href="{{ route('formSubmissions.show', $entry->id) }}" class="btn btn-sm btn-info">
                  <i class="fa fa-eye"></i>
                </a>
                <a href="{{ route('formSubmissions.destroy', $entry->id) }}"
                   onclick="return confirm('Are you sure?');" class="btn btn-sm btn-danger">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
          integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script>
    $(function() {
      // Initialize DataTable
      let table = $('#datatable').DataTable();

      // Employee Filter
      $('#employeeFilter').on('change', function() {
        table.column(2).search(this.value).draw();
      });

      // Status Filter
      $('#statusFilter').on('change', function() {
        table.column(3).search(this.value).draw();
      });

      $('#employeeFilter').select2();
    });
  </script>
@endsection
