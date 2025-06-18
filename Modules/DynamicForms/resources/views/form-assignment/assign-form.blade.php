@php
  $title = 'Assign Form';
@endphp
@extends('layouts/layoutMaster')

<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/select2/select2.scss',
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/select2/select2.js',
  ])
@endsection
@section('content')
  <div class="row mb-3">
    <div class="col">
      <div class="float-start">
        <h5 class="mt-2">{{ $title }}</h5>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('formAssignments.assign') }}" method="POST">
        @csrf
        <div class="form-group col mb-3">
          <label for="assignFor" class="control-label">Assign For</label>
          <select id="assignFor" name="assignFor" class="form-select mb-3">
            <option value="0">Teams</option>
            <option value="1">Users</option>
          </select>
        </div>

        <div class="form-group col mb-3" id="teamDiv">
          <label for="teamIds" class="control-label">Teams</label>
          <select name="teamIds[]" id="teamIds" class="form-select mb-3" data-choices multiple
                  data-options='{"removeItemButton":true,"placeholder":true}'>
            <option value="">Select teams...</option>
            @foreach ($teams as $team)
              <option
                value="{{ $team->id }}" {{ collect(old('teamIds', $assignForm->teamIds ?? []))->contains($team->id) ? 'selected' : '' }}>
                {{ $team->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group col mb-3" id="userDiv" style="display:none;">
          <label for="userIds" class="control-label">Users</label>
          <select name="userIds[]" id="userIds" class="form-select mb-3" data-choices multiple
                  data-options='{"removeItemButton":true,"placeholder":true}'>
            <option value="">Select users...</option>
            @foreach ($users as $user)
              <option
                value="{{ $user->id }}" {{ collect(old('userIds', $assignForm->userIds ?? []))->contains($user->id) ? 'selected' : '' }}>
                {{ $user->first_name }} {{ $user->last_name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group col mb-3">
          <label for="formIds" class="control-label">Forms</label>
          <select name="formIds[]" id="formIds" class="form-select mb-3" data-choices multiple
                  data-options='{"removeItemButton":true,"placeholder":true}'>
            <option value="">Select forms...</option>
            @foreach ($forms as $form)
              <option
                value="{{ $form->id }}" {{ collect(old('formIds', $assignForm->formIds ?? []))->contains($form->id) ? 'selected' : '' }}>
                {{ $form->name }}
              </option>
            @endforeach
          </select>
          @error('formIds')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <button type="submit" class="btn btn-primary">Assign</button>
      </form>
    </div>
  </div>
@endsection

@section('page-script')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"
          integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script>
    $(function() {
      $('#assignFor').on('change', function() {
        if (this.value === '0') {
          $('#teamDiv').show();
          $('#userDiv').hide();
        } else {
          $('#teamDiv').hide();
          $('#userDiv').show();
        }
      });

      $('#assignFor').select2();

      $('#teamIds').select2({
        placeholder: 'Select teams...',
        allowClear: true
      });

      $('#userIds').select2({
        placeholder: 'Select users...',
        allowClear: true
      });

      $('#formIds').select2({
        placeholder: 'Select forms...',
        allowClear: true
      });
    });
  </script>
@endsection
