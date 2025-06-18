@extends('layouts.layoutMaster')

@section('title', __('Attendances'))

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
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/select2/select2.js',
  ])
@endsection

@section('page-script')
  @vite([
    'resources/js/main-select2.js',
    'resources/assets/js/app/attendance-index.js',
  ])
@endsection

@section('content')
  <div class="container mt-4">
    <h4>Attendances</h4>
    <!-- Filter Section -->
    <div class="row mb-3">
      <div class="col-md-3 mb-3">
        <label for="date" class="form-label">Select Date</label>
        <input type="date" id="date" name="date" class="form-control"
               value="{{ request()->get('date', now()->format('Y-m-d')) }}">
      </div>
      <div class="col-md-3 mb-3">
        <label for="userId" class="form-label">Select User</label>
        <select id="userId" name="userId" class="form-select select2">
          <option value="">All Users</option>
          @foreach($users as $user)
            <option
              value="{{ $user->id }}" {{ request()->get('user') == $user->id ? 'selected' : '' }}>
              {{ $user->code }} - {{ $user->getFullName() }}
            </option>
          @endforeach
        </select>
      </div>
    </div>


    <!-- Attendance Summary Table -->
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Attendance Summary</h5>
        <div class="table-responsive">
          <table id="attendanceTable" class="table mt-3">
            <thead>
            <tr>
              <th>Id</th>
              <th>User</th>
              <th>Shift</th>
              <th>Check In Time</th>
              <th>Check Out Time</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <script>
    function filterAttendance() {
      const date = document.getElementById('date').value;
      const user = document.getElementById('user').value;
      const url = new URL(window.location.href);
      url.searchParams.set('date', date);
      if (user) url.searchParams.set('user', user);
      window.location.href = url.toString();
    }
  </script>
@endsection
