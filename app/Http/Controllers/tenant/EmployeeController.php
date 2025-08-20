<?php

namespace App\Http\Controllers\tenant;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Enums\CommonStatus;
use App\Enums\Gender;
use App\Enums\IncentiveType;
use App\Enums\Status;
use App\Enums\TargetType;
use App\Enums\UserAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Designation;
use App\Models\DocumentType;
use App\Models\DynamicQrDevice;
use App\Models\GeofenceGroup;
use App\Models\IpAddressGroup;
use App\Models\LeaveType;
use App\Models\PayrollAdjustment;
use App\Models\QrGroup;
use App\Models\Role;
use App\Models\SalesTarget;
use App\Models\Settings;
use App\Models\Shift;
use App\Models\Site;
use App\Models\Team;
use App\Models\User;
use App\Models\UserDevice;
use Constants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OwenIt\Auditing\Models\Audit;


class EmployeeController extends Controller
{

  public function addOrUpdatePayrollAdjustment(Request $request)
  {
    $validated = $request->validate([
      'id' => 'nullable|exists:payroll_adjustments,id',
      'adjustmentName' => 'required|string|max:255',
      'adjustmentCode' => 'required|string|max:191',
      'adjustmentType' => 'required|in:benefit,deduction',
      'adjustmentAmount' => 'nullable|numeric|min:0',
      'adjustmentPercentage' => 'nullable|numeric|min:0|max:100',
      'adjustmentNotes' => 'nullable|string|max:1000',
    ]);

    try {
      PayrollAdjustment::updateOrCreate(
        ['id' => $validated['id']],
        [
          'user_id' => $request->userId,
          'name' => $validated['adjustmentName'],
          'code' => $validated['adjustmentCode'],
          'type' => $validated['adjustmentType'],
          'applicability' => 'employee',
          'amount' => $validated['adjustmentAmount'] ?? 0,
          'percentage' => $validated['adjustmentPercentage'],
          'notes' => $validated['adjustmentNotes'],
          'updated_by_id' => auth()->id(),
        ]
      );

      return redirect()->back()->with('success', __('Payroll adjustment saved successfully.'));
    } catch (Exception $e) {
      Log::error('Payroll Adjustment Error: ' . $e->getMessage());
      return redirect()->back()->with('error', __('Failed to save payroll adjustment.'));
    }
  }

  public function getPayrollAdjustmentAjax($id)
  {
    $validated = validator(['id' => $id], ['id' => 'required|exists:payroll_adjustments,id'])->validate();

    $payrollAdjustment = PayrollAdjustment::find($validated['id']);

    return Success::response($payrollAdjustment);
  }

  public function addOrUpdateBankAccount(Request $request)
  {
    $validated = $request->validate([
      'userId' => 'required|exists:users,id',
      'bankName' => 'required|string|max:255',
      'bankCode' => 'required|string|max:255',
      'accountName' => 'required|string|max:255',
      'accountNumber' => 'required|string|max:255',
      'branchName' => 'required|string|max:255',
      'branchCode' => 'required|string|max:255'
    ]);

    $user = User::find($validated['userId']);

    $bank = BankAccount::where('user_id', $user->id)
      ->first();

    if ($bank) {
      $bank->bank_name = $validated['bankName'];
      $bank->bank_code = $validated['bankCode'];
      $bank->account_name = $validated['accountName'];
      $bank->account_number = $validated['accountNumber'];
      $bank->branch_name = $validated['branchName'];
      $bank->branch_code = $validated['branchCode'];
      $bank->save();
    } else {
      $user->bankAccount()->create([
        'bank_name' => $validated['bankName'],
        'bank_code' => $validated['bankCode'],
        'account_name' => $validated['accountName'],
        'account_number' => $validated['accountNumber'],
        'branch_name' => $validated['branchName'],
        'branch_code' => $validated['branchCode']
      ]);
    }

    return redirect()->back()->with('success', 'Bank account added/updated successfully');
  }

  public function create()
  {

    if (User::count() >= Settings::first()->employees_limit) {
      return redirect()->back()->with('error', 'You have reached the maximum limit of employees');
    }

    $shifts = Shift::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    $teams = Team::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    $designations = Designation::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    $users = User::where('status', UserAccountStatus::ACTIVE)
      ->where('reporting_to_id', null)
      ->where('business_id', auth()->user()->business_id)
      ->select('id', 'first_name', 'last_name', 'code')
      ->get();

    $roles = Role::whereNull('business_id')
            ->orWhere('business_id', auth()->user()->business_id)->get();

    return view('tenant.employees.create', [
      'shifts' => $shifts,
      'teams' => $teams,
      'designations' => $designations,
      'users' => $users,
      'roles' => $roles,
    ]);
  }

  public function deletePayrollAdjustment($id)
  {
    $validated = validator(['id' => $id], ['id' => 'required|exists:payroll_adjustments,id'])->validate();

    $payrollAdjustment = PayrollAdjustment::find($validated['id']);

    if ($payrollAdjustment) {
      $payrollAdjustment->delete();
    }

    return redirect()->back()->with('success', 'Payroll adjustment deleted successfully');
  }

  public function addOrUpdateSalesTarget(Request $request)
  {

    $validated = $request->validate([
      'targetId' => 'nullable|exists:sales_targets,id',
      'userId' => 'required|exists:users,id',
      'period' => 'required|numeric',
      'targetType' => ['required', Rule::in(array_column(TargetType::cases(), 'value'))],
      'incentiveType' => ['required', Rule::in(array_column(IncentiveType::cases(), 'value'))],
      'targetAmount' => 'required|numeric',
      'incentiveAmount' => 'nullable|numeric|required_if:incentiveType,flat',
      'incentivePercentage' => 'nullable|numeric|required_if:incentiveType,percentage',
      'description' => 'nullable|string|max:255',
    ]);

    $user = User::find($validated['userId']);

    $salesTarget = $user->salesTargets()
      ->where('id', $validated['targetId'])
      ->first();

    if ($salesTarget) {
      $salesTarget->target_amount = $validated['targetAmount'];
      $salesTarget->description = $validated['description'];
      $salesTarget->incentive_type = IncentiveType::from($validated['incentiveType']);
      $salesTarget->incentive_amount = $validated['incentiveAmount'] ?? 0;
      $salesTarget->incentive_percentage = $validated['incentivePercentage'] ?? 0;
      $salesTarget->save();
    } else {

      if ($user->salesTargets()->where('period', $validated['period'])->exists() && $user->salesTargets()->where('target_type', TargetType::from($validated['targetType']))->exists()) {
        return redirect()->back()->with('error', 'Sales target already exists for this period and target type');
      }

      $user->salesTargets()->create([
        'period' => $validated['period'],
        'target_type' => TargetType::from($validated['targetType']),
        'target_amount' => $validated['targetAmount'],
        'incentive_type' => IncentiveType::from($validated['incentiveType']),
        'incentive_amount' => $validated['incentiveAmount'] ?? 0,
        'incentive_percentage' => $validated['incentivePercentage'] ?? 0,
        'description' => $validated['description']
      ]);
    }

    return redirect()->back()->with('success', 'Sales target added/updated successfully');
  }

  public function destroySalesTarget($id)
  {
    $validated = validator(['id' => $id], ['id' => 'required|exists:sales_targets,id'])->validate();

    $salesTarget = SalesTarget::find($validated['id']);

    if ($salesTarget) {
      $salesTarget->delete();
    }

    return redirect()->back()->with('success', 'Sales target deleted successfully');
  }

  public function getTargetByIdAjax($id)
  {
    $validated = validator(['id' => $id], ['id' => 'required|exists:sales_targets,id'])->validate();

    $salesTarget = SalesTarget::find($validated['id']);

    return Success::response($salesTarget);
  }

  public function removeDevice(Request $request)
  {
    $validated = $request->validate([
      'userId' => 'required|exists:users,id',
    ]);

    $device = UserDevice::where('user_id', $validated['userId'])
      ->first();

    if ($device) {
      $device->delete();
    }

    return redirect()->back()->with('success', 'Device removed successfully');
  }

  public function getReportingToUsersAjax()
  {
    $users = User::where('status', UserAccountStatus::ACTIVE)
    //   ->where('reporting_to_id', null)
      ->where('business_id', auth()->user()->business_id)
      ->select('id', 'first_name', 'last_name', 'code')
      ->get();

    return Success::response($users);
  }

  public function updateWorkInformation(Request $request)
  {

    $validated = $request->validate([
      'id' => 'required|exists:users,id',
      'doj' => 'required|date',
      'teamId' => 'required|exists:teams,id',
      'shiftId' => 'required|exists:shifts,id',
      'designationId' => 'required|exists:designations,id',
      'role' => 'required|exists:roles,name',
      'reportingToId' => 'required|exists:users,id',
      'attendanceType' => 'required|in:open,geofence,ipAddress,staticqr,site,dynamicqr',
      'geofenceGroupId' => 'required_if:attendanceType,geofence|exists:geofence_groups,id',
      'ipGroupId' => 'required_if:attendanceType,ipAddress|exists:ip_address_groups,id',
      'qrGroupId' => 'required_if:attendanceType,staticqr|exists:qr_groups,id',
      'siteId' => 'required_if:attendanceType,site|exists:sites,id',
      'dynamicQrId' => 'required_if:attendanceType,dynamicqr|exists:dynamic_qr_devices,id',
    ]);

    $user = User::find($validated['id']);

    if ($user->date_of_joining != $validated['doj']) {
      $user->date_of_joining = $validated['doj'];
    }

    if ($user->team_id != $validated['teamId']) {
      $user->team_id = $validated['teamId'];
    }

    if ($user->shift_id != $validated['shiftId']) {
      $user->shift_id = $validated['shiftId'];
    }

    if ($user->designation_id != $validated['designationId']) {
      $user->designation_id = $validated['designationId'];
    }

    if ($user->reporting_to_id != $validated['reportingToId']) {
      $user->reporting_to_id = $validated['reportingToId'];
    }

    switch ($validated['attendanceType']) {
      case 'geofence':
        $user->attendance_type = 'geofence';
        $user->geofence_group_id = $validated['geofenceGroupId'];
        break;
      case 'ipAddress':
        $user->attendance_type = 'ip_address';
        $user->ip_address_group_id = $validated['ipGroupId'];
        break;
      case 'staticqr':
        $user->attendance_type = 'qr_code';
        $user->qr_group_id = $validated['qrGroupId'];
        break;
      case 'site':
        $user->attendance_type = 'site';
        $user->site_id = $validated['siteId'];
        break;
      case 'dynamicqr':
        $user->attendance_type = 'dynamic_qr';
        $user->dynamic_qr_device_id = $validated['dynamicQrId'];
        DynamicQrDevice::where('id', $validated['dynamicQrId'])
          ->update(['user_id' => $user->id, 'status' => 'in_use']);
        break;
      default:
        $user->attendance_type = 'open';
        break;
    }


    $user->save();

    // Update user role
    $role = Role::where('name', $validated['role'])->first();
    $user->roles()->sync($role->id);

    return redirect()->back()->with('success', 'Work information updated successfully');
  }

  public function updateCompensationInfo(Request $request)
  {
    $validated = $request->validate([
      'id' => 'required|exists:users,id',
      'baseSalary' => 'nullable|numeric',
      'availableLeaveCount' => 'nullable|numeric',
    ]);

    $user = User::find($validated['id']);

    if ($user->base_salary != $validated['baseSalary']) {
      $user->base_salary = $validated['baseSalary'];
    }

    if ($user->available_leave_count != $validated['availableLeaveCount']) {
      $user->available_leave_count = $validated['availableLeaveCount'];
    }

    $user->save();

    return redirect()->back()->with('success', 'Compensation info updated successfully');
  }

  public function updateBasicInfo(Request $request)
  {
    $validated = $request->validate([
      'id' => 'required|exists:users,id',
      'firstName' => 'required|string|max:255',
      'lastName' => 'required|string',
      'dob' => 'required|date',
      'gender' => ['required', Rule::in(array_column(Gender::cases(), 'value'))],
      'phone' => 'required|string|max:10',
      'altPhone' => 'nullable|string|max:10',
      'email' => 'required|email',
      'address' => 'nullable|string|max:255',
    ]);

    $user = User::find($validated['id']);

    if ($user->first_name != $validated['firstName']) {
      $user->first_name = $validated['firstName'];
    }

    if ($user->last_name != $validated['lastName']) {
      $user->last_name = $validated['lastName'];
    }

    if ($user->dob != $validated['dob']) {
      $user->dob = $validated['dob'];
    }

    if ($user->gender != $validated['gender']) {
      $user->gender = Gender::from($validated['gender']);
    }

    if ($user->phone != $validated['phone']) {
      $user->phone = $validated['phone'];
    }

    if ($user->alternate_number != $validated['altPhone']) {
      $user->alternate_number = $validated['altPhone'];
    }

    if ($user->email != $validated['email']) {
      $user->email = $validated['email'];
    }

    if ($user->address != $validated['address']) {
      $user->address = $validated['address'];
    }

    $user->save();

    return redirect()->back()->with('success', 'Basic info updated successfully');
  }

  public function index()
  {
    $active = User::where('status', UserAccountStatus::ACTIVE)->where('business_id', auth()->user()->business_id)->count();
    $inactive = User::where('status', UserAccountStatus::INACTIVE) ->where('business_id', auth()->user()->business_id)->count();
    $relieved = User::where('status', UserAccountStatus::RELIEVED) ->where('business_id', auth()->user()->business_id)->count();

    // $roles = Role::select('id', 'name')->whereNull('business_id')
    // ->orWhere('business_id', auth()->user()->business_id)
    //   ->get();
    $businessId = auth()->user()->business_id;
    $roles = Role::where(function($query) use ($businessId) {
              $query->whereNull('business_id')
                    ->orWhere('business_id', $businessId);
          })
          ->with(['users' => function ($query) use ($businessId) {
              $query->where('business_id', $businessId);
          }])
          ->get();  


    $teams = Team::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    $designations = Designation::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    return view('tenant.employees.index', [
      'totalUser' => $active + $inactive + $relieved,
      'active' => $active,
      'inactive' => $inactive,
      'relieved' => $relieved,
      'roles' => $roles,
      'teams' => $teams,
      'designations' => $designations,
    ]);
  }

  public function changeEmployeeProfilePicture(Request $request)
  {
    $rules = [
      'userId' => 'required|exists:users,id',
      'file' => 'required|image|mimes:jpeg,png,jpg|max:5096',
    ];

    $validatedData = $request->validate($rules);

    try {
      $user = User::find($request->input('userId'));

      if (!$user) {
        return Error::response('User not found');
      }

      if ($request->hasFile('file')) {
        $file = $request->file('file');
        $fileName = $user->code . '_' . time() . '.' . $file->getClientOriginalExtension();

        //Delete Old File
        $oldProfilePicture = $user->profile_picture;
        if (!is_null($oldProfilePicture)) {
          $oldProfilePicturePath = Storage::disk('public')->path(Constants::BaseFolderEmployeeProfileWithSlash . $oldProfilePicture);
          if (file_exists($oldProfilePicturePath)) {
            Storage::delete($oldProfilePicturePath);
          }
        }

        //Create Directory if not exists
        if (!Storage::disk('public')->exists(Constants::BaseFolderEmployeeProfile)) {
          Storage::disk('public')->makeDirectory(Constants::BaseFolderEmployeeProfile);
        }

        Storage::disk('public')->putFileAs(Constants::BaseFolderEmployeeProfileWithSlash, $file, $fileName);

        $user->profile_picture = $fileName;
        $user->save();
      }

      return redirect()->back()->with('success', 'Profile picture updated successfully');
    } catch (Exception $e) {
      Log::error('EmployeeController@changeEmployeeProfilePicture: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to update profile picture');
    }
  }

  public function userListAjax(Request $request)
  {
    try {
      $columns = [
        1 => 'id',
        2 => 'first_name',
        3 => 'email',
        4 => 'email_verified_at',
        5 => 'status',
        6 => 'code',
        7 => 'phone',
        8 => 'role',
        9 => 'profile_picture',
      ];

      $search = [];

      $totalData = User::where('business_id', auth()->user()->business_id)->count();

      $totalFiltered = $totalData;

      $limit = $request->input('length');
      $start = $request->input('start');
      $order = $columns[$request->input('order.0.column')];
      $dir = $request->input('order.0.dir');

      $query = User::query()->where('business_id', auth()->user()->business_id);

      if ($request->has('roleFilter') && !empty($request->input('roleFilter'))) {
        $query->whereHas('roles', function ($q) use ($request) {
          $q->where('name', $request->input('roleFilter'));
        });
      }

      if ($request->has('teamFilter') && !empty($request->input('teamFilter'))) {
        $query->where('team_id', $request->input('teamFilter'));
      }

      if ($request->has('designationFilter') && !empty($request->input('designationFilter'))) {
        $query->where('designation_id', $request->input('designationFilter'));
      }


      if (empty($request->input('search.value'))) {
        $users = $query->offset($start)
          ->limit($limit)
          ->orderBy($order, $dir)
          ->get();

      } else {
        $search = $request->input('search.value');

        $users = $query->where('id', 'LIKE', "%{$search}%")
          ->orWhere('first_name', 'LIKE', "%{$search}%")
          ->orWhere('last_name', 'LIKE', "%{$search}%")
          ->orWhere('phone', 'LIKE', "%{$search}%")
          ->orWhere('email', 'LIKE', "%{$search}%")
          ->offset($start)
          ->limit($limit)
          ->orderBy($order, $dir)
          ->get();

        $totalFiltered = $query->where('id', 'LIKE', "%{$search}%")
          ->orWhere('first_name', 'LIKE', "%{$search}%")
          ->orWhere('last_name', 'LIKE', "%{$search}%")
          ->orWhere('phone', 'LIKE', "%{$search}%")
          ->orWhere('email', 'LIKE', "%{$search}%")
          ->count();
      }

      $data = [];

      $baseUrlProfilePicture = asset('storage/'.Constants::BaseFolderEmployeeProfile);
      if (!empty($users)) {
        foreach ($users as $user) {
          $nestedData['id'] = $user->id;
          $nestedData['name'] = $user->getFullName() ?? '';
          $nestedData['attendance_type'] = $user->attendance_type;
          $nestedData['team'] = $user->team->name ?? '';
          $nestedData['email'] = $user->email;
          $nestedData['email_verified_at'] = $user->email_verified_at;
          $nestedData['status'] = $user->status;
          $nestedData['code'] = $user->code;
          $nestedData['phone'] = $user->phone;
          $nestedData['role'] = $user->roles()->first()->name ?? '';
          $nestedData['profile_picture'] = $user->profile_picture != null ? $baseUrlProfilePicture . '/' . $user->profile_picture : null;

          $data[] = $nestedData;
        }
      }

      return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'code' => 200,
        'data' => $data,
      ]);
    } catch (Exception $e) {
      Log::error('EmployeeController@userListAjax: ' . $e->getMessage());
      return Error::response($e->getMessage());
    }
  }

  public function deleteEmployeeAjax($id)
  {
    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    try {
      $user = User::find($id);

      if (!$user) {
        return Error::response('User not found');
      }

      $user->delete();

      return Success::response('User deleted successfully');
    } catch (Exception $e) {
      Log::error('EmployeeController@deleteEmployeeAjax: ' . $e->getMessage());
      return Error::response('Failed to delete user');
    }
  }

  public function show($id)
  {
    validator(['id' => $id], ['id' => 'required|exists:users,id'])->validate();

    $user = User::where('id', $id)
      ->with('userDevice')
      ->with('team')
      ->with('userAvailableLeaves')
      ->with('shift')
      ->with('designation')
      ->with('salesTargets')
      ->with('bankAccount')
      ->first();

    $documentTypes = DocumentType::where('status', CommonStatus::ACTIVE)
      ->get();

    $leaveTypes = LeaveType::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    return view('tenant.employees.view', [
      'user' => $user,
      'documentTypes' => $documentTypes,
      'leaveTypes' => $leaveTypes,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'firstName' => 'required|string|max:255',
      'lastName' => 'required|string|max:255',
      'gender' => ['required', Rule::in(array_column(Gender::cases(), 'value'))],
      'phone' => 'required|string|max:10|unique:users,phone',
      'altPhone' => 'nullable|string|max:10',
      'email' => 'required|email|unique:users,email',
      'role' => 'required|exists:roles,name',
      'dob' => 'required|date',
      'address' => 'nullable|string|max:255',
      'file' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
      'useDefaultPassword' => 'nullable',
      'password' => 'nullable|min:6',
      'confirmPassword' => 'nullable|min:6|same:password',

      'code' => 'required|string|max:255|unique:users,code',
      'designationId' => 'required|exists:designations,id',
      'doj' => 'required|date',
      'teamId' => 'required|exists:teams,id',
      'shiftId' => 'required|exists:shifts,id',
      'reportingToId' => 'required|exists:users,id',
      'attendanceType' => 'required|in:open,geofence,ipAddress,staticqr,dynamicqr,site',
      'geofenceGroupId' => 'required_if:attendanceType,geofence|exists:geofence_groups,id',
      'ipGroupId' => 'required_if:attendanceType,ipAddress|exists:ip_address_groups,id',
      'qrGroupId' => 'required_if:attendanceType,staticqr|exists:qr_groups,id',
      'siteId' => 'required_if:attendanceType,site|exists:sites,id',
      'dynamicQrId' => 'required_if:attendanceType,dynamicqr|exists:dynamic_qr_devices,id',

      'baseSalary' => 'required|numeric',
      'availableLeaveCount' => 'nullable|numeric',
    ]);

    try {
      $user = new User();
      $user->first_name = $request->input('firstName');
      $user->last_name = $request->input('lastName');
      $user->gender = Gender::from($request->input('gender'));
      $user->phone = $request->input('phone');
      $user->alternate_number = $request->input('altPhone');
      $user->email = $request->input('email');
      $user->dob = $request->input('dob');
      $user->business_id = auth()->user()->business_id;
      $user->address = $request->input('address');

      if ($request->has('useDefaultPassword') && $request->input('useDefaultPassword') == 'on') {
        $user->password = bcrypt(Settings::first()->default_password ?? 123456);
      } else {
        $user->password = bcrypt($request->input('password'));
      }

      $user->code = $request->input('code');
      $user->date_of_joining = $request->input('doj');
      $user->team_id = $request->input('teamId');
      $user->shift_id = $request->input('shiftId');
      $user->reporting_to_id = $request->input('reportingToId');
      $user->designation_id = $request->input('designationId');
      $user->base_salary = $request->input('baseSalary');

      //Attendance Type Settings
      switch ($request->input('attendanceType')) {
        case 'geofence':
          $user->attendance_type = 'geofence';
          $user->geofence_group_id = $request->input('geofenceGroupId');
          break;
        case 'ipAddress':
          $user->attendance_type = 'ip_address';
          $user->ip_address_group_id = $request->input('ipGroupId');
          break;
        case 'staticqr':
          $user->attendance_type = 'qr_code';
          $user->qr_group_id = $request->input('qrGroupId');
          break;
        case 'site':
          $user->attendance_type = 'site';
          $user->site_id = $request->input('siteId');
          break;
        case 'dynamicqr':
          $user->attendance_type = 'dynamic_qr';
          $user->dynamic_qr_device_id = $request->input('dynamicQrId');
          DynamicQrDevice::where('id', $request->input('dynamicQrId'))
            ->update(['user_id' => $user->id, 'status' => 'in_use']);
          break;
        default:
          $user->attendance_type = 'open';
          break;
      }

      $user->status = UserAccountStatus::ACTIVE;

      if ($request->hasFile('file')) {

        $file = $request->file('file');
        $fileName = $user->code . '_' . time() . '.' . $file->getClientOriginalExtension();

        //Create Directory if not exists
        if (!Storage::disk('public')->exists(Constants::BaseFolderEmployeeProfile)) {
          Storage::disk('public')->makeDirectory(Constants::BaseFolderEmployeeProfile);
        }

        Storage::disk('public')->putFileAs(Constants::BaseFolderEmployeeProfileWithSlash, $file, $fileName);

        $user->profile_picture = $fileName;
      }

      $user->created_by_id = auth()->id();
      $user->save();

      $user->assignRole($request->input('role'));


      return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    } catch (Exception $e) {
      Log::error('EmployeeController@store: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to create employee');
    }
  }

  public function checkEmailValidationAjax(Request $request)
  {
    $email = $request->input('email');

    if (!$email) {
      return response()->json([
        "valid" => false,
      ]);
    }

    //Edit case handling
    if ($request->has('id')) {
      $id = $request->input('id');
      if (User::where('email', $email)->where('id', '!=', $id)->exists()) {
        return response()->json([
          "valid" => false,
        ]);
      } else {
        return response()->json([
          "valid" => true,
        ]);
      }
    }

    if (User::where('email', $email)->exists()) {
      return response()->json([
        "valid" => false,
      ]);
    }

    return response()->json([
      "valid" => true,
    ]);
  }

  public function checkPhoneValidationAjax(Request $request)
  {

    $phone = $request->input('phone');

    if (!$phone) {
      return response()->json([
        "valid" => false,
      ]);
    }

    //Edit Case Handling
    if ($request->has('id')) {
      $id = $request->input('id');
      if (User::where('phone', $phone)->where('id', '!=', $id)->withTrashed()->exists()) {
        return response()->json([
          "valid" => false,
        ]);
      } else {
        return response()->json([
          "valid" => true,
        ]);
      }
    }

    if (User::where('phone', $phone)->withTrashed()->exists()) {
      return response()->json([
        "valid" => false,
      ]);
    }

    return response()->json([
      "valid" => true,
    ]);
  }

  public function checkEmployeeCodeValidationAjax(Request $request)
  {
    $code = $request->input('code');

    if (!$code) {
      return response()->json([
        "valid" => false,
      ]);
    }

    //Edit Case Handling
    if ($request->has('id')) {
      $id = $request->input('id');
      if (User::where('code', $code)->where('id', '!=', $id)->withTrashed()->exists()) {
        return response()->json([
          "valid" => false,
        ]);
      } else {
        return response()->json([
          "valid" => true,
        ]);
      }
    }

    if (User::where('code', $code)->withTrashed()->exists()) {
      return response()->json([
        "valid" => false,
      ]);
    }

    return response()->json([
      "valid" => true,
    ]);
  }

  public function getGeofenceGroups()
  {
    $geofenceGroups = GeofenceGroup::where('status', '=', 'active')
      ->select('id', 'name')
      ->get();

    return response()->json($geofenceGroups);
  }

  public function getIpGroups()
  {
    $ipGroups = IpAddressGroup::where('status', '=', 'active')
      ->select('id', 'name')
      ->get();

    return response()->json($ipGroups);
  }

  public function getQrGroups()
  {
    $qrGroups = QrGroup::where('status', '=', 'active')
      ->select('id', 'name')
      ->get();

    return response()->json($qrGroups);
  }

  public function getDynamicQrDevices()
  {
    $devices = DynamicQrDevice::where('user_id', null)
      ->where('site_id', null)
      ->get();

    return response()->json($devices);
  }

  public function getSites()
  {
    $sites = Site::where('status', '=', 'active')
      ->select('id', 'name')
      ->get();

    return response()->json($sites);
  }

  public function toggleStatus($id)
  {
    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $user = User::find($id);

    if ($user->status == UserAccountStatus::ACTIVE) {
      $user->status = UserAccountStatus::INACTIVE;
    } else {
      $user->status = UserAccountStatus::ACTIVE;
    }

    $user->save();

    return Success::response('Status updated successfully');
  }

  public function relieveEmployee($id)
  {
    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $user = User::find($id);

    if ($user) {
      $user->status = UserAccountStatus::RELIEVED;
      $user->relieved_at = now();
      $user->save();
    }

    return Success::response('Employee relieved successfully');
  }

  public function retireEmployee($id)
  {
    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $user = User::find($id);

    if ($user) {
      $user->status = UserAccountStatus::RETIRED;
      $user->retired_at = now();
      $user->save();
    }

    return Success::response('Employee retired successfully');
  }

  public function myProfile()
  {
    $user = User::find(auth()->user()->id);

    $auditLogs = Audit::where('user_id', auth()->user()->id)
      ->where('auditable_type', 'App\Models\User')
      ->orderBy('created_at', 'desc')
      ->get();

    $role = $user->roles()->first();

    return view('account.my-profile', [
      'user' => $user,
      'auditLogs' => $auditLogs,
      'role' => $role,
    ]);
  }
}
