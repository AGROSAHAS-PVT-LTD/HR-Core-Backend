@php
  $title = 'Assigned Forms';
@endphp
  <!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  ])
@endsection
@extends('layouts/layoutMaster')
@section('content')
  <div class="row mb-3">
    <div class="col align-content-center">
      <div class="float-start">
        <h5 class="mt-2">{{ $title }}</h5>
      </div>
    </div>
    <div class="col">
      <div class="float-end">
        <div class="row g-3">
          <div class="col-auto">
            <a href="{{ route('formAssignments.assignForm') }}" class="btn btn-secondary">
              <span class="fa fa-plus-circle fa-fw me-2"></span>Assign Form
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow">
    <div class="card-body">
      <table id="datatable" class="table">
        <thead>
        <tr>
          <th>ID</th>
          <th>Form</th>
          <th>Assigned To</th>
          <th>Submissions</th>
          <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($formAssignments as $assignment)
          <tr>
            <td>{{ $assignment->id }}</td>
            <td>{{ $assignment->form->name }}</td>
            <td>
              @if ($assignment->team)
                <p>Team ({{ $assignment->team->name }})</p>
              @else
                <p>User ({{ $assignment->user->getFullName() }})</p>
              @endif
            </td>
            <td>{{ $assignment->form->entries->count() }}</td>
            <td class="d-flex flex-row">
              <div class="p-1">
                <a href="{{ route('formAssignments.destroy', $assignment->id) }}"
                   class="btn btn-phoenix-danger"
                   data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"
                   onclick="return confirm('Are you sure you want to remove?');">
                  <i class="fa fa-trash"></i>
                </a>
              </div>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('page-script')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
          integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script>
    $(function() {
      $('#datatable').dataTable();
    });
  </script>
@endsection
