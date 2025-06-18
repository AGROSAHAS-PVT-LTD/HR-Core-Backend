<?php

namespace App\Http\Controllers\tenant;

use App\Enums\UserAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
  public function index()
  {
    $users = User::where('status', UserAccountStatus::ACTIVE)
      ->get();

    $attendances = Attendance::where('created_at', date('Y-m-d'))
      ->first();

    $logs = AttendanceLog::get();

    return view('tenant.attendance.index', [
      'users' => $users,
      'attendances' => $attendances ?? [],
      'attendanceLogs' => $logs ?? [],
    ]);
  }

  public function indexAjax(Request $request)
  {
    $query = Attendance::query()
      ->with('attendanceLogs');

    //User filter
    if ($request->has('userId') && $request->input('userId')) {
      Log::info('User ID: ' . $request->input('userId'));
      $query->where('user_id', $request->input('userId'));
    }

    if ($request->has('date') && $request->input('date')) {
      Log::info('Date: ' . $request->input('date'));
      $query->whereDate('created_at', $request->input('date'));
    } else {
      $query->whereDate('created_at', Carbon::today());
    }

    return DataTables::of($query)
      ->addColumn('id', function ($attendance) {
        return $attendance->id;
      })
      ->editColumn('check_in_time', function ($attendance) {
        $checkInAt = $attendance->attendanceLogs->where('type', 'check_in')
          ->first();
        return $checkInAt ? $checkInAt->created_at->format('h:i A') : 'N/A';
      })
      ->editColumn('check_out_time', function ($attendance) {
        $checkOutAt = $attendance->attendanceLogs->where('type', 'check_out')
          ->last();
        return $checkOutAt ? $checkOutAt->created_at->format('h:i A') : 'N/A';
      })
      ->addColumn('shift', function ($attendance) {
        return $attendance->shift ? $attendance->shift->name : 'N/A';
      })
      ->addColumn('user', function ($attendance) {
        $employeeViewUrl = 'employees/view/';
        if ($attendance->user->profile_picture) {
          $profileOutput = '<img src="' . asset(Constants::BaseFolderEmployeeProfileWithSlash . $attendance->user->profile_picture) . '"  alt="Avatar" class="avatar rounded-circle " />';
        } else {
          $profileOutput = '<span class="avatar-initial rounded-circle bg-label-info">' . $attendance->user->getInitials() . '</span>';
        }

        return '<div class="d-flex justify-content-start align-items-center user-name">' .
          '<div class="avatar-wrapper">' .
          '<div class="avatar avatar-sm me-4">' .
          $profileOutput .
          '</div>' .
          '</div>' .
          '<div class="d-flex flex-column">' .
          '<a href="' .
          $employeeViewUrl .
          $attendance->user_id .
          '" class="text-heading text-truncate"><span class="fw-medium">' .
          $attendance->user->getFullName() .
          '</span></a>' .
          '<small>' .
          $attendance->user->code .
          '</small>' .
          '</div>' .
          '</div>';

      })
      ->rawColumns(['user'])
      ->make(true);
  }
}
