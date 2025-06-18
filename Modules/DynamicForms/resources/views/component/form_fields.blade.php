@php
  use App\Models\FormField;
  $formFields = FormField::where('form_id', $form->id)->get();
@endphp

<div class="card">
  <div class="card-header">
    <h5>Fields</h5>
  </div>
  <div class="card-body">
    @if ($formFields->isNotEmpty())
      <div class="table-responsive">
        <table class="table table-striped" id="dataTable">
          <thead>
          <tr>
            <td>{{ __('ID') }}</td>
            <td>{{ __('Field Type') }}</td>
            <td>{{ __('Label') }}</td>
            <td>{{ __('Placeholder') }}</td>
            <td>{{ __('Values') }}</td>
            <td>{{ __('Is Required') }}</td>
            <td>{{ __('Created At') }}</td>
          </tr>
          </thead>
          <tbody>
          @foreach ($formFields as $field)
            <tr>
              <td>{{ $field->id }}</td>
              <td>{{ $field->field_type }}</td>
              <td>{{ $field->label }}</td>
              <td>{{ $field->placeholder }}</td>
              <td>{{ $field->values ?? "N/A" }}</td>
              <td>
                @if($field->is_required)
                  <span class="badge bg-success">Yes</span>
                @else
                  <span class="badge bg-danger">No</span>
                @endif
              </td>
              <td>{{ $field->created_at }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @else
      <p>No fields added!</p>
    @endif
  </div>
</div>
