@php
  $title = 'Payment Collections';
  $currencySymbol = $settings->currency_symbol;
@endphp
@section('title', $title)
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
  <div class="row mb-3 align-items-center">
    <div class="col">
      <h4 class="mt-2">{{ $title }}</h4>
    </div>
    <div class="col-md-4 text-end">

    </div>
  </div>

  <!-- Filters Section -->
  <div class="row mb-4">
    <!-- Employee Filter -->
    <div class="col-md-3 mb-3">
      <label for="employeeFilter" class="form-label">Filter by employee</label>
      <select id="employeeFilter" name="employeeFilter" class="form-select select2">
        <option value="" selected>All Employees</option>
        @foreach($employees as $employee)
          <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
        @endforeach
      </select>
    </div>

    <!--Date Filter -->
    <div class="col-md-3 mb-3">
      <label for="dateFilter" class="form-label">Filter by date</label>
      <input type="date" id="dateFilter" name="dateFilter" class="form-control">
    </div>
  </div>

  <div class="card shadow">
    <div class="card-body">
      <table id="datatable" class="table">
        <thead>
        <tr>
          <th>
            Sl.No
          </th>
          <th>
            User
          </th>
          <th>
            Client
          </th>
          <th>
            Amount
          </th>
          <th>
            Created On
          </th>
        </tr>
        </thead>
        <tbody>
        @foreach ($paymentCollections as $item)
          <tr data-employee="{{ $item->user_id }}" data-date="{{$item->created_at}}">
            <td>
              {{$loop->iteration}}
            </td>
            <td>
              @include('_partials._profile-avatar',[
  'user' => $item->user,
])
            </td>
            <td>
              {{$item->client->name}}
            </td>
            <td>
              {{$currencySymbol}}{{$item->amount}}
            </td>
            <td>
              {{$item->created_at->format(Constants::DateTimeFormat)}}
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
    $(function () {
      $('#datatable').dataTable();

      $('#employeeFilter').select2();

      $('#employeeFilter').on('change', function () {
        const employeeId = $(this).val();
        $('#datatable tbody tr').each(function () {
          if (employeeId === '') {
            $(this).show();
          } else {
            const dataEmployee = $(this).data('employee');
            if (dataEmployee == employeeId) {
              $(this).show();
            } else {
              $(this).hide();
            }
          }
        });
      });

      $('#dateFilter').on('change', function () {
        const date = $(this).val();
        console.log(date);
        $('#datatable tbody tr').each(function () {
          if (date === '') {
            $(this).show();
          } else {
            const dataDate = $(this).data('date');
            if (dataDate.includes(date)) {
              $(this).show();
            } else {
              $(this).hide();
            }
          }
        });
      });
    });
  </script>
@endsection
