@php
  $title = 'Task View'
@endphp
@extends('layouts/layoutMaster')
@section('title', $title)
@section('content')
  <div class="row mb-3">
    <div class="col">
      <div class="float-start">
        <h4 class="mt-2"> {{$title}}</h4>
      </div>
    </div>
    <div class="col">
    </div>
  </div>

  @if($tasks->count() > 0)
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-4 g-3 mb-9">
      @foreach($tasks as $task)
        <div class="col">
          <div class="card h-100 hover-actions-trigger">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <h4 class="mb-2 line-clamp-1 lh-sm flex-1 me-5">{{$task->title}}</h4>
                <div class="hover-actions top-0 end-0 mt-4 me-4">
                  <a class="btn btn-primary btn-icon flex-shrink-0"
                     href="{{route('task.activity', $task->id)}}" target="_blank"><span
                      class="fa-solid fa-chevron-right"></span></a>
                </div>
              </div>

              <div class="d-flex align-items-center mb-2">
                @if ($task->type == 'client_based')

                  <span
                    class="badge badge-phoenix fs-10 mb-2 badge-phoenix-primary">Client based</span>

                @else

                  <span
                    class="badge badge-phoenix fs-10 mb-2 badge-phoenix-secondary">Open</span>
                @endif
              </div>
              @if ($task->type == 'client_based')
                <div class="d-flex align-items-center mb-2">
                  <span class="fa-solid fa-user me-2 text-body-tertiary fs-9 fw-extra-bold"></span>
                  <p class="fw-bold mb-0 text-truncate lh-1">Client : <span
                      class="fw-semibold text-primary ms-1"> {{$task->client->name}}</span></p>
                </div>
              @endif
              <div class="d-flex align-items-center mb-4">
                <span class="fa-solid fa-user me-2 text-body-tertiary fs-9 fw-extra-bold"></span>
                <p class="fw-bold mb-0 lh-1">Employee : <span
                    class="ms-1 text-body-emphasis">{{$task->user->first_name.' '.$task->user->last_name}}</span>
                </p>
              </div>
              <div class="d-flex justify-content-between text-body-tertiary fw-semibold">
                <p class="mb-2"> Progress</p>
                @if ($task->status == 'in_progress')

                  <span
                    class="badge badge-phoenix fs-10 mb-2 badge-phoenix-success">In Progress</span>
                @elseif ($task->status == 'hold')

                  <span
                    class="badge badge-phoenix fs-10 mb-2 badge-phoenix-warning">Hold</span>
                @endif
              </div>
              <div class="progress bg-success-subtle">
                <div
                  class="progress-bar progress-bar-striped progress-bar-animated rounded "
                  role="progressbar" aria-label="Success example" style="width:
                                    100%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                </div>
              </div>
              <div class="d-flex align-items-center mt-4">
                <p class="mb-0 fw-bold fs-9">Started :<span
                    class="fw-semibold text-body-tertiary text-opactity-85 ms-1">{{$task->started_at}}</span>
                </p>
              </div>

              <div
                class="d-flex d-lg-block d-xl-flex justify-content-between align-items-center mt-3">
                <div class="">
                </div>
                <div class="mt-lg-3 mt-xl-0">
                  <i class="fa-solid fa-list-check me-1"></i>
                  <p class="d-inline-block fw-bold mb-0">{{$task->taskUpdates->count()}}<span
                      class="fw-normal">	Updates</span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div> No active tasks found.</div>
  @endif
@endsection
