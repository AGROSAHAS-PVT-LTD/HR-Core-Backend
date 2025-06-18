<?php

namespace Modules\DynamicForms\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormAssignment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class FormAssignmentController extends Controller
{
    public function index()
    {
        $formAssignments = FormAssignment::all();

        return view('dynamicforms::form-assignment.index', compact('formAssignments'));
    }

    public function assignForm()
    {
        $forms = Form::where('status', 'active')->get();

        $users = User::where('status', 'active')
            ->get();

        $teams = Team::where('status', 'active')->get();

        return view('dynamicforms::form-assignment.assign-form', [
            'forms' => $forms,
            'users' => $users,
            'teams' => $teams,
        ]);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'assignFor' => 'required|in:0,1', // 0 for Teams, 1 for Users
            'teamIds' => 'required_if:assignFor,0|array',
            'teamIds.*' => 'exists:teams,id',
            'userIds' => 'required_if:assignFor,1|array',
            'userIds.*' => 'exists:users,id',
            'formIds' => 'required|array',
            'formIds.*' => 'exists:forms,id',
        ]);

        $currentUserId = auth()->id();

        foreach ($request->formIds as $formId) {
            if ($request->assignFor == 0) {
                foreach ($request->teamIds as $teamId) {
                    FormAssignment::create([
                        'form_id' => $formId,
                        'team_id' => $teamId,
                        'user_id' => null,
                        'created_by_id' => $currentUserId,
                        'updated_by_id' => $currentUserId,
                    ]);
                }
            } else { // Assign to Users
                foreach ($request->userIds as $userId) {
                    FormAssignment::create([
                        'form_id' => $formId,
                        'user_id' => $userId,
                        'team_id' => null,
                        'created_by_id' => $currentUserId,
                        'updated_by_id' => $currentUserId,
                    ]);
                }
            }
        }

        return redirect()->route('formAssignments.index')->with('success', 'Form assignments created successfully');

    }

    public function destroy($id)
    {
        FormAssignment::destroy($id);

        return redirect()->route('formAssignments.index')->with('success', 'Form assignment deleted successfully');
    }
}
