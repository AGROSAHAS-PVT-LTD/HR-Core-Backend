<?php

namespace Modules\DynamicForms\App\Http\Controllers\Api;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormAssignment;
use App\Models\FormEntry;
use App\Models\FormEntryField;
use Illuminate\Http\Request;

class DynamicFormsApiController extends Controller
{
  public function getAssignedForms()
  {
    $authUser = auth()->user();

    $forms = FormAssignment::where('user_id', $authUser->id)
      ->with('form')
      ->with('form.fields')
      ->get();

    $response = [];

    foreach ($forms as $form) {
      if ($form->form->status != 'active') {
        continue;
      }
      $fields = [];

      foreach ($form->form->fields as $field) {
        if (($field->field_type == 'select' || $field->field_type == 'multiselect') && !$field->values) {
          continue;
        }
        $fields[] = [
          'id' => $field->id,
          'isRequired' => $field->is_required,
          'label' => $field->label,
          'placeholder' => $field->placeholder,
          'type' => $field->field_type,
          'values' => $field->values ? explode(',', $field->values) : [],
        ];
      }

      $entriesCount = FormEntry::where('form_id', $form->form->id)
        ->where('user_id', $authUser->id)
        ->count();

      $response[] = [
        'id' => $form->form->id,
        'name' => $form->form->name,
        'description' => $form->form->description,
        'formId' => $form->form->id,
        'isClientRequired' => $form->form->is_client_required,
        'formFields' => $fields,
        'entriesCount' => $entriesCount,
      ];
    }

    return Success::response($response);
  }

  public function submitForm(Request $request)
  {
    $formId = $request->formId;

    $formLines = $request->formLines;

    if (!$formId) {
      return Error::response('Form ID is required');
    }

    if (!$formLines) {
      return Error::response('Form lines are required');
    }

    if (!is_array($formLines)) {
      return Error::response('Form lines should be an array');
    }

    if (count($formLines) == 0) {
      return Error::response('Form lines should not be empty');
    }

    $form = Form::find($formId);

    if (!$form) {
      return Error::response('Form not found');
    }

    if ($form->is_client_required && !$request->clientId) {
      return Error::response('Client ID is required');
    }

    $authUserId = auth()->user()->id;

    $formEntry = new FormEntry();
    $formEntry->form_id = $formId;
    $formEntry->user_id = $authUserId;
    $formEntry->client_id = $request->clientId;
    $formEntry->created_by_id = $authUserId;

    $formEntry->save();

    foreach ($formLines as $formLine) {
      $formEntryField = new FormEntryField();
      $formEntryField->form_entry_id = $formEntry->id;
      $formEntryField->form_field_id = $formLine['id'];
      $formEntryField->value = $formLine['value'];
      $formEntryField->created_by_id = $authUserId;
      $formEntryField->save();
    }

    return Success::response('Form submitted successfully');

  }
}
