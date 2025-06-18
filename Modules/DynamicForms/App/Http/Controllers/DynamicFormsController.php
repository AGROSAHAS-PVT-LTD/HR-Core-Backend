<?php

namespace Modules\DynamicForms\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DynamicFormsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $forms = Form::all();

        return view('dynamicforms::index', compact('forms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dynamicforms::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'isClientRequired' => 'required',
        ]);

        $form = new Form();
        $form->name = $validated['name'];
        $form->description = $validated['description'];
        $form->is_client_required = $validated['isClientRequired'];
        $form->save();

        return redirect()->route('forms.index')->with('success', 'Form created successfully');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('dynamicforms::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $form = Form::find($id);

        return view('dynamicforms::edit', compact('form'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'required|exists:forms,id',
            'name' => 'required',
            'description' => 'required',
            'isClientRequired' => 'required',
        ]);

        $form = Form::find($validated['id']);
        $form->name = $validated['name'];
        $form->description = $validated['description'];
        $form->is_client_required = $validated['isClientRequired'];
        $form->save();

        return redirect()->route('forms.index')->with('success', 'Form updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function changeStatus(Request $request)
    {
        $form = Form::find($request->input('id'));
        $form->status = $form->status == 'active' ? 'inactive' : 'active';
        $form->save();

        return response()->json('Status updated');
    }

    public function addFields($formId)
    {
        $form = Form::find($formId);

        return view('dynamicforms::add_fields', compact('form'));
    }

    public function storeFields(Request $request)
    {
        $validated = $request->validate([
            'formId' => 'required|exists:forms,id',
            'fieldType' => 'required',
            'placeholder' => 'required',
            'label' => 'required',
            'isRequired' => 'required',
            'values' => 'nullable|required_if:fieldType,select,multiselect',
        ]);

        $form = Form::find($validated['formId']);

        $field = new FormField();
        $field->form_id = $form->id;
        $field->field_type = $validated['fieldType'];
        $field->placeholder = $validated['placeholder'];
        $field->label = $validated['label'];
        $field->is_required = $validated['isRequired'];
        $field->values = $validated['values'];
        $field->created_by_id = auth()->id();
        $field->save();

        return redirect()->route('forms.addFields', $form->id)->with('success', 'Field added successfully');
    }
}
