@php
  $title = 'Tasks'
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
    <div class="col">
      <div class="float-start">
        <h4 class="mt-2">{{$title}}</h4>
      </div>
    </div>
    <div class="col">
      <div class="float-end">
        <a href="{{route('task.create')}}" class="btn btn-primary"><span
            class="fa fa-plus-circle fa-fw me-2"></span>Create new</a>
      </div>
    </div>
  </div>


  <div class="row justify-content-center mb-3">
    <div class="col-12 col-md-4 col-sm-12 col-xl-3 mb-3">
      <div class="card shadow radius-10">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="">
              <p class="mb-1">Total</p>
              <h4 class="mb-0 text-pink">
                {{$tasks->count()}}
              </h4>
            </div>
            <div class="ms-auto fs-2 text-pink">
              <i class="bi bi-people"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 col-sm-12 col-xl-3 mb-3">
      <div class="card shadow radius-10">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="">
              <p class="mb-1">Pending</p>
              <h4 class="mb-0 text-pink">
                {{$tasks->where('status', 'new')->count()}}
              </h4>
            </div>
            <div class="ms-auto fs-2 text-pink">
              <i class="bi bi-people"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 col-sm-12 col-xl-3 mb-3">
      <div class="card shadow radius-10">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="">
              <p class="mb-1">Ongoing</p>
              <h4 class="mb-0 text-success">
                {{$tasks->where('status', 'in_progress')->count()}}
              </h4>
            </div>
            <div class="ms-auto fs-2 text-success">
              <i class="bi bi-person-check"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 col-sm-12 col-xl-3 mb-3">
      <div class="card shadow radius-10">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="">
              <p class="mb-1">Hold</p>
              <h4 class="mb-0 text-warning">
                {{$tasks->where('status', 'hold')->count()}}
              </h4>
            </div>
            <div class="ms-auto fs-2 text-warning">
              <i class="bi bi-person-dash"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="card shadow">
    <div class="card-body">
      <table id="datatable" class="table">
        <thead>
        <th class="ps-2">
          Sl.No
        </th>
        <th>
          Employee
        </th>
        <th>
          Title
        </th>
        <th>
          Task Type
        </th>
        <th>
          For Date
        </th>
        <th>
          Start Date Time
        </th>
        <th>
          End Date Time
        </th>
        <th>
          Status
        </th>
        <th>Action</th>
        </thead>
        <tbody>
        @foreach($tasks as $task)
          <tr>
            <td class="ps-2">
              {{$loop->iteration}}
            </td>
            <td>
              {{$task->user->first_name.' '.$task->user->last_name}}
            </td>
            <td>
              {{$task->title}}
            </td>
            <td>
              {{$task->type}}
            </td>
            <td>
              {{$task->for_date}}
            </td>
            <td>
              {{$task->start_date_time}}
            </td>
            <td>
              {{$task->end_date_time}}
            </td>
            <td>
              @if($task->status == 'new')
                <span class="badge bg-danger">New</span>
              @elseif($task->status == 'completed')
                <span class="badge bg-success">Completed</span>
              @else
                <span class="badge bg-warning">{{$task->status}}</span>
              @endif
            </td>
            <td class="d-flex d-flex-row">
              @if($task->status != 'new')
                <a href="{{route('task.activity', $task->id)}}"
                   data-bs-toggle="tooltip" data-bs-placement="top" title="Activities"
                   class="btn btn-phoenix-primary"> <i class="fa fa-list-check"></i></a>
              @else
                <form action="{{route('task.destroy', $task->id)}}" method="post"
                      class="d-inline">
                  @csrf
                  @method('delete')
                  <button type="submit" class="btn btn-sm btn-phoenix-danger" data-bs-toggle="tooltip"
                          data-bs-placement="left" title="Delete task"
                          onclick="return confirm('Are you sure you want to delete?')">
                    <i class="fa fa-trash"></i>
                  </button>
                </form>
              @endif
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
