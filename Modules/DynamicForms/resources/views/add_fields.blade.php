@php
  $title = 'Add Fields';
@endphp
@section('title', $title)
@extends('layouts/layoutMaster')

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
@section('content')
  <div class="row">
    <div class="col-12 col-md-6 col-lg-4 mb-4">
      <div class="card h-100 ">
        <div class="card-header">
          <h5>Add Field</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('forms.storeFields') }}" method="POST">
            @csrf
            <input type="hidden" name="formId" value="{{ $form->id }}">

            <div class="form-group">
              <label for="label" class="control-label">Label<span class="text-danger">*</span></label>
              <input type="text" name="label" id="label" class="form-control"
                     value="{{ old('label') }}">
              @error('label')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div class="form-group mt-3">
              <label for="placeholder" class="control-label">Placeholder</label>
              <input type="text" name="placeholder" id="placeholder" class="form-control"
                     value="{{ old('placeholder') }}">
              @error('placeholder')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div class="row">
              <div class="form-group col-6 mt-3">
                <label for="fieldType" class="control-label">Field Type</label>
                <select name="fieldType" id="fieldType" class="form-select">
                  <option
                    value="text" {{ old('fieldType', $formField->fieldType ?? '') == 'text' ? 'selected' : '' }}>
                    Text
                  </option>
                  <option value="number"
                    {{ old('fieldType', $formField->fieldType ?? '') == 'number' ? 'selected' : '' }}>
                    Number
                  </option>
                  <option value="date"
                    {{ old('fieldType', $formField->fieldType ?? '') == 'date' ? 'selected' : '' }}>
                    Date
                  </option>
                  <option value="time"
                    {{ old('fieldType', $formField->fieldType ?? '') == 'time' ? 'selected' : '' }}>
                    Time
                  </option>
                  <option value="boolean"
                    {{ old('fieldType', $formField->fieldType ?? '') == 'boolean' ? 'selected' : '' }}>
                    Boolean
                  </option>
                  <option value="select"
                    {{ old('fieldType', $formField->fieldType ?? '') == 'select' ? 'selected' : '' }}>
                    Select
                  </option>
                  <option value="multiselect"
                    {{ old('fieldType', $formField->fieldType ?? '') == 'multiselect' ? 'selected' : '' }}>
                    Multi Select
                  </option>
                  <option value="url"
                    {{ old('fieldType', $formField->fieldType ?? '') == 'url' ? 'selected' : '' }}>
                    URL
                  </option>
                  <option value="email"
                    {{ old('fieldType', $formField->fieldType ?? '') == 'email' ? 'selected' : '' }}>
                    Email
                  </option>
                  <option value="address"
                    {{ old('fieldType', $formField->fieldType ?? '') == 'address' ? 'selected' : '' }}>
                    Address
                  </option>
                </select>
                @error('fieldType')
                <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="form-group col-6 mt-3">
                <label for="isRequired" class="control-label">Is Required</label>
                <select name="isRequired" id="isRequired" class="form-control">
                  <option
                    value="1" {{ old('isRequired', $formField->isRequired ?? '') == '1' ? 'selected' : '' }}>
                    Yes
                  </option>
                  <option
                    value="0" {{ old('isRequired', $formField->isRequired ?? '') == '0' ? 'selected' : '' }}>
                    No
                  </option>
                </select>
                @error('isRequired')
                <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
            </div>

            <div class="form-group mt-3" style="display: none;" id="valuesDiv">
              <label for="values" class="control-label">Values (Comma separated values without
                space)</label>
              <input type="text" name="values" id="values" class="form-control"
                     value="{{ old('values', $formField->values ?? '') }}">
              @error('values')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div class="row p-3">
              <button type="submit" class="btn btn-primary">Add</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 col-lg-8">
      @include('dynamicforms::component.form_fields', ['formId' => $form->id])
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      $('#fieldType').on('change', function() {
        if (this.value === 'select' || this.value === 'multiselect') {
          $('#valuesDiv').show();
        } else {
          $('#valuesDiv').hide();
        }
      });

      $('#dataTable').dataTable();
    });
  </script>
@endsection
