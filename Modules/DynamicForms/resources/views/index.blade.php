@php
  $title = 'Forms';
@endphp
@section('title', $title)
@extends('layouts/layoutMaster')
@section('content')
  <div class="row mb-3">
    <div class="col">
      <div class="float-start">
        <h4 class="mt-2">{{ $title }}</h4>
      </div>
    </div>
    <div class="col">
      <div class="float-end">
        <a href="{{ route('forms.create') }}" class="btn btn-primary">
          <span class="fa fa-plus-circle fa-fw me-2"></span>Create Form
        </a>
      </div>
    </div>
  </div>

  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-4 g-3 mb-9">
    @if ($forms->isNotEmpty())
      @foreach ($forms as $form)
        <div class="col">
          <div class="card h-100 hover-actions-trigger">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <h4 class="mb-2 line-clamp-1 lh-sm flex-1 me-5">{{ $form->name }}</h4>
                <div class="hover-actions top-0 end-0 mt-4 me-4">
                  <a href="{{ route('forms.edit', $form->id) }}"
                     class="btn btn-primary btn-icon flex-shrink-0" data-bs-toggle="tooltip"
                     data-bs-placement="top" title="Edit Form">
                    <span class="fa-solid fa-edit"></span>
                  </a>
                </div>
              </div>
              <span
                class="badge badge-phoenix fs--2 mb-4 {{ $form->status === 'active' ? 'badge-phoenix-success' : 'badge-phoenix-warning' }}">
                            {{ $form->status }}
                        </span>
              <div class="d-flex justify-content-between text-700 fw-semi-bold">
                <p class="mb-2">Entries</p>
                <p class="mb-2 text-1100">{{ $form->entries->count() }}</p>
              </div>
              <div
                class="progress {{ $form->entries->isEmpty() ? 'bg-danger-100' : 'bg-success-100' }}">
                <div
                  class="progress-bar rounded {{ $form->entries->isEmpty() ? 'bg-danger' : 'bg-success' }}"
                  role="progressbar"
                  style="width: {{ $form->entries->isEmpty() ? '2%' : '100%' }}" aria-valuemin="0"
                  aria-valuemax="100"></div>
              </div>
              <div class="d-flex align-items-center mt-4">
                <p class="mb-0 fw-bold fs--1">Total Fields: <span
                    class="fw-semi-bold text-600 ms-1">{{ $form->entries->count() }}</span></p>
              </div>
              <div class="d-flex align-items-center mt-2">
                <p class="mb-0 fw-bold fs--1">Requires Client: <span
                    class="fw-semi-bold text-600 ms-1">{{ $form->is_client_required ? 'Yes' : 'No' }}</span>
                </p>
              </div>
              <div class="d-flex align-items-center mt-2">
                <p class="mb-0 fw-bold fs--1">Created On: <span
                    class="fw-semi-bold text-600 ms-1">{{ $form->created_at->format('d-m-Y') }}</span>
                </p>
              </div>
              <div class="d-flex d-lg-block d-xl-flex justify-content-between align-items-center mt-3">
                <div class="avatar-group mt-3">
                  <p class="me-2">Status</p>
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox"
                           {{ $form->status === 'active' ? 'checked' : '' }} onchange="changeStatus({{ $form->id }})">
                  </div>
                </div>
                <div class="d-flex flex-row">
                  <div class="p-1">
                    <a href="{{ route('forms.addFields', ['formId' => $form->id]) }}"
                       class="btn btn-primary" data-bs-toggle="tooltip"
                       data-bs-placement="top" title="Add Fields">
                      <i class="bx bx-plus"></i>
                    </a>
                  </div>
                  {{-- <div class="p-1">
                      <a href="{{ route('forms.delete', $form->id) }}" class="btn btn-phoenix-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                          <i class="fa fa-trash"></i>
                      </a>
                  </div> --}}
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    @else
      <p>No forms added!</p>
    @endif
  </div>
@endsection

@section('page-script')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
          integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script>
    function changeStatus(id) {
      $.ajax({
        'csrf-token': '{{csrf_token()}}',
        url: "{{route('forms.changeStatus')}}",
        type: 'POST',
        dataType: 'json',
        data: {
          id: id,
          _token: "{{ csrf_token() }}"
        },
        success: function(data) {
          console.log(data);
          //Reload the page after 2 seconds
          setTimeout(() => {
            location.reload();
          }, 2000);
        },
        error: function(data) {
        }
      });
    }
  </script>
@endsection
