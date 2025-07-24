<?php

namespace App\Http\Controllers\tenant;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\Shift;
use Exception;
use Illuminate\Http\Request;

class ShiftController extends Controller
{

  public function getShiftListAjax()
  {
    $shifts = Shift::where('status', Status::ACTIVE)
      ->get(['id', 'name', 'code']);
    return Success::response($shifts);
  }

  public function index()
  {
    return view('tenant.shift.index');
  }

  public function getShiftsListAjax(Request $request)
  {
    try {
      $columns = [
        1 => 'id',
        2 => 'name',
        3 => 'notes',
        4 => 'code',
        5 => 'status',
      ];

      $search = [];

      $totalData = Shift::count();

      $totalFiltered = $totalData;

      $limit = $request->input('length');
      $start = $request->input('start');
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');

      if (empty($request->input('search.value'))) {
        $shifts = Shift::offset($start)
          ->limit($limit)
          ->orderBy($order, $dir)
          ->get();
      } else {
        $search = $request->input('search.value');
        $shifts = Shift::where('id', 'like', "%{$search}%")
          ->orWhere('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%")
          ->get();

        $totalFiltered = Shift::where('id', 'like', "%{$search}%")
          ->orWhere('name', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%")
          ->count();
      }

      $data = [];
      if (!empty($shifts)) {
        foreach ($shifts as $shifts) {
          $nestedData['id'] = $shifts->id;
          $nestedData['name'] = $shifts->name;
          $nestedData['code'] = $shifts->code;
          $nestedData['notes'] = $shifts->notes;
          $nestedData['sunday'] = $shifts->sunday;
          $nestedData['monday'] = $shifts->monday;
          $nestedData['tuesday'] = $shifts->tuesday;
          $nestedData['wednesday'] = $shifts->wednesday;
          $nestedData['thursday'] = $shifts->thursday;
          $nestedData['friday'] = $shifts->friday;
          $nestedData['saturday'] = $shifts->saturday;
          $nestedData['status'] = $shifts->status;
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
      return Error::response($e->getMessage());
    }
  }

  public function addOrUpdateShiftAjax(Request $request)
  {
    $shiftId = $request->id;
    $request->validate([
      'name' => 'required',
      'notes' => 'nullable',
      'code' => ['required', 'unique:shifts,code,' . $shiftId],

    ]);

    if ($shiftId) {

      $shift = Shift::findOrFail($shiftId);

      $shift->name = $request->name;
      $shift->notes = $request->notes;
      $shift->code = $request->code;
      $shift->start_time = $request->startTime;
      $shift->end_time = $request->endTime;
      $shift->sunday = $request->sunday;
      $shift->monday = $request->monday;
      $shift->tuesday = $request->tuesday;
      $shift->wednesday = $request->wednesday;
      $shift->thursday = $request->thursday;
      $shift->friday = $request->friday;
      $shift->saturday = $request->saturday;
      $shift->save();

      return response()->json([
        'code' => 200,
        'message' => 'Updated',
      ]);
    } else {
      $shift = new Shift();
      $shift->name = $request->name;
      $shift->notes = $request->notes;
      $shift->code = $request->code;
      $shift->start_time = $request->startTime;
      $shift->end_time = $request->endTime;
      $shift->status = $request->status;
      $shift->sunday = $request->sunday;
      $shift->monday = $request->monday;
      $shift->tuesday = $request->tuesday;
      $shift->wednesday = $request->wednesday;
      $shift->thursday = $request->thursday;
      $shift->friday = $request->friday;
      $shift->saturday = $request->saturday;
      $shift->status = Status::ACTIVE;
      $shift->start_date = now();

      $shift->save();

      return response()->json([
        'code' => 200,
        'message' => 'Added',
      ]);
    }
  }
  public function checkCodeValidationAjax(Request $request)
  {
    $code = $request->code;


    if (!$code) {
      return response()->json(["valid" => false]);
    }

    if ($request->has('id')) {
      $id = $request->input('id');
      if (Shift::where('code', $code)->where('id', '!=', $id)->exists()) {
        return response()->json([
          "valid" => false,
        ]);
      } else {
        return response()->json([
          "valid" => true,
        ]);
      }
    }
    if (Shift::where('code', $code)->exists()) {
      return response()->json([
        "valid" => false,
      ]);
    }
    return response()->json([
      "valid" => true,
    ]);
  }

  public function getShiftAjax($id)
  {
    $shift = Shift::findOrFail($id);

    if (!$shift) {
      return Error::response('Shift not found');
    }

    $response = [
      'id' => $shift->id,
      'name' => $shift->name,
      'notes' => $shift->notes,
      'code' => $shift->code,
      'startTime' => $shift->start_time,
      'endTime' => $shift->end_time,
      'sunday' => $shift->sunday,
      'monday' => $shift->monday,
      'tuesday' => $shift->tuesday,
      'wednesday' => $shift->wednesday,
      'thursday' => $shift->thursday,
      'friday' => $shift->friday,
      'saturday' => $shift->saturday,
    ];

    return response()->json($response);
  }


  public function deleteShiftAjax($id)
  {
    try {
      $shift = Shift::findOrFail($id);

      if (!$shift) {
        return Error::response('Shift not found');
      }

      $shift->delete();

      return Success::response('Shift deleted successfully');
    } catch (Exception $e) {
      return Error::response('An error occurred while deleting the shift: ' . $e->getMessage());
    }
  }

  public function changeStatus($id)
  {
    $shift = Shift::findOrFail($id);

    if (!$shift) {
      return response()->json([
        'code' => 404,
        'message' => 'Shift not found',
      ]);
    }
    $shift->status = $shift->status == Status::ACTIVE ? Status::INACTIVE : Status::ACTIVE;
    $shift->save();
    return response()->json([
      'code' => 200,
      'message' => 'Shift status changed successfully',
    ]);
  }
}
