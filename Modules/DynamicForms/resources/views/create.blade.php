@php
  $title = 'Create Form';
@endphp
@section('title', $title)
@extends('layouts/layoutMaster')
@section('content')
  <form action="{{ route('forms.store') }}" method="POST">
    @csrf
    <div class="card">
      <div class="card-body">
        <!-- Display validation errors -->
        @if ($errors->any())
          <div class="text-danger">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="row">
          <div class="col">
            <div class="form-group mt-3">
              <label for="name" class="control-label">Name<span class="text-danger">*</span></label>
              <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
              @error('name')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="col mt-3">
            <div class="form-group">
              <label for="isClientRequired" class="control-label">Is Client Required<span
                  class="text-danger">*</span></label>
              <select name="isClientRequired" id="isClientRequired" class="form-control">
                <option value="1" {{ old('isClientRequired') == '1' ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('isClientRequired') == '0' ? 'selected' : '' }}>No</option>
              </select>
              @error('isClientRequired')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>

        <div class="form-group mt-3">
          <label for="description" class="control-label">Description</label>
          <textarea name="description" id="description"
                    class="form-control">{{ old('description') }}</textarea>
          @error('description')
          <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>
      </div>
      <div class="card-footer">
        <div class="form-group">
          <button type="submit" class="btn btn-primary">Create</button>
        </div>
      </div>
    </div>
  </form>
@endsection

@section('scripts')
@endsection
