@php
  $title = 'Submission Details';
@endphp

@section('title', $title)
@extends('layouts/layoutMaster')

@section('content')
  <div class="row gy-4">
    <!-- Form Details Section -->
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Form Details</h5>
        </div>
        <div class="card-body">
          <table class="table table-borderless">
            <tr>
              <td><label class="fw-bold">Form Name</label></td>
              <td>{{ $formEntry->form->name }}</td>
            </tr>
            <tr>
              <td><label class="fw-bold">Filled By</label></td>
              <td>{{ $formEntry->user->getFullName() }}</td>
            </tr>
            <tr>
              <td><label class="fw-bold">Submitted On</label></td>
              <td>{{ $formEntry->created_at->format(Constants::DateTimeFormat) }}</td>
            </tr>
            @if ($formEntry->client)
              <tr>
                <td><label class="fw-bold">Client</label></td>
                <td>{{ $formEntry->client->name }}</td>
              </tr>
              <tr>
                <td><label class="fw-bold">Client Number</label></td>
                <td>{{ $formEntry->client->phoneNumber }}</td>
              </tr>
            @endif
          </table>
        </div>
      </div>
    </div>

    <!-- Form Entries Section -->
    <div class="col-12 col-lg-8">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Form Entries</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table id="datatable" class="table table-striped mb-0">
              <thead class="bg-light">
              <tr>
                <th class="text-center">ID</th>
                <th>Field Type</th>
                <th>Field Name</th>
                <th>Value</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($formEntry->formEntryFields as $entryField)
                <tr>
                  <td class="text-center">{{ $entryField->id }}</td>
                  <td>{{ ucfirst($entryField->formField->field_type) }}</td>
                  <td>
                    {{ $entryField->formField->label }}
                    @if ($entryField->formField->is_required)
                      <span class="text-danger">*</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-secondary text-wrap">{{ $entryField->value }}</span>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
