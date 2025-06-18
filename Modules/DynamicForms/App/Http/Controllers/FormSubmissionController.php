<?php

namespace Modules\DynamicForms\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FormEntry;
use App\Models\User;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
  public function index(Request $request)
  {

    if ($request->has('employeeId')) {
      $formEntries = FormEntry::with('form')
        ->with('formEntryFields')
        ->with('user')
        ->where('user_id', $request->employeeId)
        ->get();
    } else {

      $formEntries = FormEntry::with('form')
        ->with('formEntryFields')
        ->with('user')
        ->get();
    }

    $users = User::whereNot('shift_id', null)
      ->get();

    return view('dynamicforms::form-submission.index', [
      'formEntries' => $formEntries,
      'users' => $users
    ]);
  }

  public function show($id)
  {
    $formEntry = FormEntry::with('form')
      ->with('formEntryFields')
      ->with('user')
      ->find($id);

    return view('dynamicforms::form-submission.show', [
      'formEntry' => $formEntry
    ]);
  }

  public function destroy($id)
  {
    $formEntry = FormEntry::find($id);

    if ($formEntry) {
      $formEntry->delete();
    }

    return redirect()->route('formSubmissions.index');
  }
}
