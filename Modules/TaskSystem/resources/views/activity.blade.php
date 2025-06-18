@php
  $title = 'Task Activities';
@endphp

@extends('layouts/layoutMaster')

@section('title', $title)

@section('page-style')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css"/>
@endsection

@section('content')
  <div class="row">
    <!-- Task Details Section -->
    <div class="col-xl-4 mb-4">
      <div class="card">
        <h5 class="card-header">Task Details</h5>
        <div class="card-body">
          <table class="table table-bordered">
            <tbody>
            <tr>
              <td><strong>ID</strong></td>
              <td>{{ $task->id }}</td>
            </tr>
            <tr>
              <td><strong>Title</strong></td>
              <td>{{ $task->title }}</td>
            </tr>
            <tr>
              <td><strong>Description</strong></td>
              <td>{{ $task->description }}</td>
            </tr>
            <tr>
              <td><strong>Task Type</strong></td>
              <td>{{ $task->type }}</td>
            </tr>
            <tr>
              <td><strong>For Date</strong></td>
              <td>{{ $task->for_date }}</td>
            </tr>
            <tr>
              <td><strong>Start Date Time</strong></td>
              <td>{{ $task->started_at ?? 'N/A' }}</td>
            </tr>
            <tr>
              <td><strong>End Date Time</strong></td>
              <td>{{ $task->ended_at ?? 'N/A' }}</td>
            </tr>
            <tr>
              <td><strong>Task Status</strong></td>
              <td>
                @switch($task->status)
                  @case('in_progress') <span class="badge bg-success">In Progress</span> @break
                  @case('new') <span class="badge bg-warning">New</span> @break
                  @case('completed') <span class="badge bg-primary">Completed</span> @break
                  @case('hold') <span class="badge bg-danger">Hold</span> @break
                  @default <span class="badge bg-secondary">{{ $task->status }}</span>
                @endswitch
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add Comments / Document Form -->
      <div class="card mt-3">
        <h5 class="card-header">Add Comments / Document</h5>
        <div class="card-body">
          <form action="{{ route('task.addTaskUpdate') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="TaskId" name="TaskId" value="{{ $task->id }}"/>
            <div class="mb-3">
              <label class="form-label">Message Type</label>
              <select class="form-control" id="MessageType" name="MessageType">
                <option value="1">Message</option>
                <option value="2">Document</option>
              </select>
            </div>
            <div class="mb-3" id="msgDiv">
              <label class="form-label">Message</label>
              <textarea id="Comments" name="Comments" class="form-control"
                        placeholder="Enter message here"></textarea>
            </div>
            <div class="mb-3" id="fileDiv" style="display:none;">
              <label class="form-label">Document</label>
              <input type="file" id="File" name="File" class="form-control"/>
            </div>
            <button type="submit" class="btn btn-primary">Add</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Task Activities Timeline -->
    <div class="col-xl-8">
      <div class="card">
        <h5 class="card-header">Task Activities Timeline</h5>
        <div class="card-body">
          <ul class="timeline mb-0">
            @foreach ($task->taskUpdates as $update)
              @switch($update->update_type)
                @case('start')
                @case('complete')
                @case('hold')
                @case('un_hold')
                  <li class="timeline-item timeline-item-transparent">
                    <span class="timeline-point timeline-point-primary"></span>
                    <div class="timeline-event">
                      <div class="timeline-header mb-2">
                        <h6 class="mb-0">{{ ucfirst($update->update_type) }}</h6>
                        <small
                          class="text-muted">{{ $update->created_at->format(Constants::DateTimeHumanFormat) }}</small>
                      </div>
                    </div>
                  </li>
                  @break

                @case('location')
                  <li class="timeline-item timeline-item-transparent">
                    <span class="timeline-point timeline-point-success"></span>
                    <div class="timeline-event">
                      <div class="timeline-header mb-2">
                        <h6 class="mb-0">📍 Shared Location</h6>
                        <small class="text-muted">{{ $update->created_at->diffForHumans() }}</small>
                      </div>
                      <a
                        href="https://www.google.com/maps/search/?api=1&query={{ $update->latitude }},{{ $update->longitude }}"
                        target="_blank">View on Map</a>
                    </div>
                  </li>
                  @break

                @case('image')
                  <li class="timeline-item timeline-item-transparent">
                    <span class="timeline-point timeline-point-info"></span>
                    <div class="timeline-event">
                      <h6 class="mb-2">🖼️ Shared an Image</h6>
                      <a href="{{ asset('storage/'.Constants::BaseFolderTaskUpdateFiles . $update->file_url) }}"
                         class="glightbox">
                        <img src="{{ asset('storage/'.Constants::BaseFolderTaskUpdateFiles . $update->file_url) }}"
                             alt="Image"
                             class="img-thumbnail" width="150"/>
                      </a>
                    </div>
                  </li>
                  @break

                @case('document')
                  <li class="timeline-item timeline-item-transparent">
                    <span class="timeline-point timeline-point-warning"></span>
                    <div class="timeline-event">
                      <h6 class="mb-2">📄 Shared a Document @if($update->is_admin)
                          <span class="badge bg-primary">Admin</span>
                        @endif</h6>
                      <a href="{{ asset('storage/'.Constants::BaseFolderTaskUpdateFiles . $update->file_url) }}"
                         class="btn btn-outline-primary btn-sm" target="_blank">View Document</a>
                    </div>
                  </li>
                  @break

                @case('comment')
                  <li class="timeline-item timeline-item-transparent">
                    <span class="timeline-point timeline-point-secondary"></span>
                    <div class="timeline-event">
                      <h6 class="mb-2">💬 Comment @if($update->is_admin)
                          <span class="badge bg-primary">Admin</span>
                        @endif</h6>
                      <p>{{ $update->comment }}</p>
                    </div>
                  </li>
                  @break
              @endswitch
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
  <script>
    $(function () {
      const lightbox = GLightbox();

      $('#MessageType').on('change', function () {
        $('#msgDiv').toggle($(this).val() == 1);
        $('#fileDiv').toggle($(this).val() == 2);
      });
    });
  </script>
@endsection
