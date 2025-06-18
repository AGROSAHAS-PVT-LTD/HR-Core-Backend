<?php

namespace App\Http\Controllers\tenant;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Designation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DesignationController extends Controller
{

  public function getDesignationListAjax()
  {
    $designations = Designation::where('status', Status::ACTIVE)
      ->get(['id', 'name', 'code']);

    return Success::response($designations);
  }

  public function index()
  {
    return view('tenant.designation.index');
  }

  public function indexAjax(Request $request)
  {
    try {
      $columns = [
        1 => 'id',
        2 => 'name',
        3 => 'code',
        4 => 'departmentId',
        5 => 'notes',
        6 => 'status',
      ];


      $query = Designation::query();

      $limit = $request->input('length');
      $start = $request->input('start');
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');

      $totalData = $query->count();

      if ($order == 'id') {
        $order = 'designations.id';
        $query->orderBy($order, $dir);
      }

      if (empty($request->input('search.value'))) {

        $designations = $query->select('designations.*', 'departments.name as department_name')
          ->leftJoin('departments as departments', 'designations.department_id', '=', 'departments.id')
          ->offset($start)
          ->limit($limit)
          ->get();
      } else {
        $search = $request->input('search.value');

        $designations = $query->select('designations.*', 'departments.name as department_name')
          ->leftJoin('departments as departments', 'designations.department_id', '=', 'departments.id')
          ->where(function ($query) use ($search) {
            $query->where('designations.id', 'like', "%{$search}%")
              ->orWhere('designations.name', 'like', "%{$search}%")
              ->orWhere('designations.code', 'like', "%{$search}%")
              ->orWhere('designations.notes', 'like', "%{$search}%");
          })
          ->offset($start)
          ->limit($limit)
          ->get();


      }

      $totalFiltered = $query->count();

      $data = [];
      if (!empty($designations)) {
        foreach ($designations as $designation) {
          $nestedData['id'] = $designation->id;
          $nestedData['name'] = $designation->name;
          $nestedData['code'] = $designation->code;
          $nestedData['notes'] = $designation->notes;
          $nestedData['department'] = $designation->department_name;
          $nestedData['status'] = $designation->status;

          Log::info($nestedData);
          $data[] = $nestedData;
        }
      }

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'code' => 200,
        'data' => $data
      ]);
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return Error::response('Something went wrong');
    }
  }


  public function addOrUpdateAjax(Request $request)
  {
    $designationId = $request->id;
    $request->validate([
      'name' => 'required',
      'code' => ['required', Rule::unique('designations')->ignore($designationId)],
      'department_id' => 'nullable|exists:departments,id',
      'notes' => 'nullable',
    ]);

    try {
      if ($designationId) {
        $designation = Designation::findOrFail($designationId);
        if (!$designation) {
          return Error::response('Designation not found', 404);
        }
        $designation->name = $request->name;
        $designation->code = $request->code;
        $designation->notes = $request->notes;
        $designation->department_id = $request->department_id;
        $designation->save();
        return Success::response('Updated');
      } else {
        $designation = new Designation();
        $designation->name = $request->name;
        $designation->code = $request->code;
        $designation->notes = $request->notes;
        $designation->department_id = $request->department_id;
        $designation->save();
        return Success::response('Added');
      }
    } catch (Exception $e) {
      return Error::response($e->getMessage());
    }
  }

  public function getByIdAjax($id)
  {

    if (!$designation = Designation::findOrFail($id)) {
      return Error::response('Designation not found', 404);
    }

    $response = [
      'id' => $designation->id,
      'name' => $designation->name,
      'code' => $designation->code,
      'notes' => $designation->notes,
      'department_id' => $designation->department_id,
      'status' => $designation->status,
    ];

    return response()->json($response);

  }


  public function deleteAjax($id)
  {
    $designation = Designation::findOrFail($id);
    if (!$designation) {
      return Error::response('Designation not found');
    }
    $designation->delete();
    return Success::response('Designation deleted successfully');
  }

  public function changeStatus($id)
  {
    $designation = Designation::findOrFail($id);
    if (!$designation) {
      return Error::response('Designation not found', 404);
    }
    $designation->status = $designation->status == Status::ACTIVE ? Status::INACTIVE : Status::ACTIVE;
    $designation->save();
    return Success::response('Designation status changed successfully');
  }

  public function checkCodeValidationAjax(Request $request)
  {
    $code = $request->code;


    if (!$code) {
      return response()->json(["valid" => false]);
    }

    if ($request->has('id')) {
      $id = $request->input('id');
      if (Designation::where('code', $code)->where('id', '!=', $id)->exists()) {
        return response()->json([
          "valid" => false,
        ]);
      } else {
        return response()->json([
          "valid" => true,
        ]);
      }
    }
    if (Designation::where('code', $code)->exists()) {
      return response()->json([
        "valid" => false,
      ]);
    }
    return response()->json([
      "valid" => true,
    ]);
  }
}
