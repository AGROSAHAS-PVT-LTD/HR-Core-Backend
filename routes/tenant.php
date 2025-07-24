<?php

declare(strict_types=1);

use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\CheckSubscription;



use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\tenant\AttendanceController;
use App\Http\Controllers\tenant\ClientController;
use App\Http\Controllers\tenant\DashboardController;
use App\Http\Controllers\tenant\DepartmentsController;
use App\Http\Controllers\tenant\DesignationController;
use App\Http\Controllers\tenant\DeviceController;
use App\Http\Controllers\tenant\EmployeeController;
use App\Http\Controllers\tenant\ExpenseController;
use App\Http\Controllers\tenant\ExpenseTypeController;
use App\Http\Controllers\tenant\HolidayController;
use App\Http\Controllers\tenant\LeaveController;
use App\Http\Controllers\tenant\LeaveTypeController;
use App\Http\Controllers\tenant\OrganisationHierarchyController;
use App\Http\Controllers\tenant\PermissionController;
use App\Http\Controllers\tenant\ReportController;
use App\Http\Controllers\tenant\SettingsController;
use App\Http\Controllers\tenant\ShiftController;
use App\Http\Controllers\tenant\SOSController;
use App\Http\Controllers\tenant\TeamController;
use App\Http\Controllers\tenant\VisitController;
use App\Services\AddonService\IAddonService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;


/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
*/

Route::middleware([
  'web',
])->group(function () {


  Route::get('/', [SuperAdminController::class, 'defaultHome'])->name('home');
  Route::get('/register/{package_id}', [SuperAdminController::class, 'defaultRegister'])->name('home.business.register');
  Route::post('/register', [SuperAdminController::class, 'defaultRegisterStore'])->name('home.business.store');
  Route::get('/postFlutterwavePaymentCallback', [SuperAdminController::class, 'postFlutterwavePaymentCallback'])->name('home.postFlutterwavePaymentCallback');

  Route::get('/auth/login', [AuthController::class, 'login'])->name('auth.login');
  Route::post('/auth/login', [AuthController::class, 'loginPost'])->name('auth.loginPost');
  Route::get('/accessDenied', [BaseController::class, 'accessDenied'])->name('accessDenied');

  Route::middleware(['auth', CheckSubscription::class])->group(callback: function () {

    Route::group(['middleware' => function ($request, $next) {
      $addonService = app(IAddonService::class);
      if (!$addonService->isAddonEnabled(ModuleConstants::SOS, true)) {
        return redirect()->route('accessDenied')->with('error', 'You do not have permission to access this page');
      }
      return $next($request);
    }], function () {
      Route::get('/sos', [SOSController::class, 'index'])->name('sos.index');
      Route::get('/sos/fetch', [SOSController::class, 'fetchSOSRequests'])->name('sos.fetch');
      Route::post('/sos/resolve/{id}', [SOSController::class, 'markAsResolved'])->name('sos.resolve');
    });

    Route::post('markAsRead', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/myNotifications', [NotificationController::class, 'myNotifications'])->name('notifications.myNotifications');
    Route::get('getNotificationsAjax', [NotificationController::class, 'getNotificationsAjax'])->name('notifications.getNotificationsAjax');

    Route::get('/', [DashboardController::class, 'index'])->name('tenant.dashboard');
    Route::get('liveLocation', [DashboardController::class, 'liveLocationView'])->name('liveLocationView');
    Route::get('liveLocationAjax', [DashboardController::class, 'liveLocationAjax'])->name('liveLocationAjax');
    Route::get('cardView', [DashboardController::class, 'cardView'])->name('cardView');
    Route::get('cardViewAjax', [DashboardController::class, 'cardViewAjax'])->name('cardViewAjax');
    Route::get('timeline', [DashboardController::class, 'timelineView'])->name('timelineView');
    Route::post('getTimeLineAjax', [DashboardController::class, 'getTimeLineAjax'])->name('getTimeLineAjax');
    Route::get('getRecentActivities', [DashboardController::class, 'getRecentActivities'])->name('getRecentActivities');


    Route::get('getAttendanceLogAjax/{userId}/{date}', [DashboardController::class, 'getAttendanceLogAjax'])->name('getAttendanceLogAjax');
    Route::get('getStatsForTimeLineAjax/{userId}/{date}/{attendanceLogId}', [DashboardController::class, 'getStatsForTimeLineAjax'])->name('getStatsForTimeLineAjax');
    Route::get('getActivityAjax/{userId}/{date}/{attendanceLogId}', [DashboardController::class, 'getActivityAjax'])->name('getActivityAjax');
    Route::get('getDeviceLocationAjax/{userId}/{date}/{attendanceLogId}', [DashboardController::class, 'getDeviceLocationAjax'])->name('getDeviceLocationAjax');
    Route::get('getDepartmentPerformanceAjax', [DashboardController::class, 'getDepartmentPerformanceAjax'])->name('getDepartmentPerformanceAjax');

    Route::prefix('settings/')->name('settings.')->group(function () {
      Route::get('', [SettingsController::class, 'index'])->name('index');
      Route::post('updateGeneralSettings', [SettingsController::class, 'updateGeneralSettings'])->name('updateGeneralSettings');
      Route::post('updateCompanySettings', [SettingsController::class, 'updateCompanySettings'])->name('updateCompanySettings');
      Route::post('updateAppSettings', [SettingsController::class, 'updateAppSettings'])->name('updateAppSettings');
      Route::post('updateTrackingSettings', [SettingsController::class, 'updateTrackingSettings'])->name('updateTrackingSettings');
      Route::post('updateMapSettings', [SettingsController::class, 'updateMapSettings'])->name('updateMapSettings');
      Route::post('updateEmployeeSettings', [SettingsController::class, 'updateEmployeeSettings'])->name('updateEmployeeSettings');
      Route::post('updatePayrollSettings', [SettingsController::class, 'updatePayrollSettings'])->name('updatePayrollSettings');
      Route::post('updateAiSettings', [SettingsController::class, 'updateAiSettings'])->name('updateAiSettings');

      //Payroll
      Route::post('addOrUpdatePayrollAdjustment', [SettingsController::class, 'addOrUpdatePayrollAdjustment'])->name('addOrUpdatePayrollAdjustment');
      Route::delete('deletePayrollAdjustment/{id}', [SettingsController::class, 'deletePayrollAdjustment'])->name('deletePayrollAdjustment');
    });

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('report/getAttendanceReport', [ReportController::class, 'getAttendanceReport'])->name('report.getAttendanceReport');
    Route::post('report/getVisitReport', [ReportController::class, 'getVisitReport'])->name('report.getVisitReport');
    Route::post('report/getLeaveReport', [ReportController::class, 'getLeaveReport'])->name('report.getLeaveReport');
    Route::post('report/getExpenseReport', [ReportController::class, 'getExpenseReport'])->name('report.getExpenseReport');
    Route::post('reports/getProductOrderReport', [ReportController::class, 'getProductOrderReport'])->name('report.getProductOrderReport');


    Route::get('leaves', [LeaveController::class, 'index'])->name('leaves.index');

    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');

    Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index');

    Route::prefix('attendance/')->name('attendance.')->group(function () {
      Route::get('', [AttendanceController::class, 'index'])->name('index');
      Route::get('indexAjax', [AttendanceController::class, 'indexAjax'])->name('indexAjax');
    });

    Route::get('visits', [VisitController::class, 'index'])->name('visits.index');

    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

    Route::get('/lang/{locale}', [LanguageController::class, 'swap']);


    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles/addOrUpdateAjax', [RoleController::class, 'addOrUpdateAjax'])->name('roles.addOrUpdateAjax');
    Route::delete('roles/deleteAjax/{id}', [RoleController::class, 'deleteAjax'])->name('roles.deleteAjax');

    //Search Routes
    Route::get('/getSearchDataAjax', [BaseController::class, 'getSearchDataAjax'])->name('search.Ajax');

    Route::prefix('holidays/')->name('holidays.')->group(function () {
      Route::get('', [HolidayController::class, 'index'])->name('index');
      Route::get('indexAjax', [HolidayController::class, 'indexAjax'])->name('indexAjax');
      Route::post('addOrUpdateHolidayAjax', [HolidayController::class, 'addOrUpdateHolidayAjax'])->name('addOrUpdateHolidayAjax');
      Route::get('getByIdAjax/{id}', [HolidayController::class, 'getByIdAjax'])->name('getByIdAjax');
      Route::delete('deleteAjax/{id}', [HolidayController::class, 'deleteAjax'])->name('deleteAjax');
      Route::post('changeStatusAjax/{id}', [HolidayController::class, 'changeStatusAjax'])->name('changeStatusAjax');
    });

    Route::prefix('employees/')->name('employees.')->group(function () {
      Route::get('', [EmployeeController::class, 'index'])->name('index');
      Route::get('view/{id}', [EmployeeController::class, 'show'])->name('show');
      Route::post('indexAjax', [EmployeeController::class, 'userListAjax'])->name('indexAjax');
      Route::get('create', [EmployeeController::class, 'create'])->name('create');
      Route::get('getNewEmployeeCode/{locationId}', [EmployeeController::class, 'GetNewEmployeeCodeByLocationAjax'])->name('getNewEmployeeCode');
      Route::get('checkEmailValidationAjax', [EmployeeController::class, 'checkEmailValidationAjax'])->name('checkEmailValidationAjax');
      Route::get('checkPhoneValidationAjax', [EmployeeController::class, 'checkPhoneValidationAjax'])->name('checkPhoneValidationAjax');
      Route::get('checkEmployeeCodeValidationAjax', [EmployeeController::class, 'checkEmployeeCodeValidationAjax'])->name('checkEmployeeCodeValidationAjax');
      Route::delete('deleteEmployeeAjax/{id}', [EmployeeController::class, 'deleteEmployeeAjax'])->name('deleteEmployeeAjax');
      Route::post('store', [EmployeeController::class, 'store'])->name('store');

      Route::post('changeEmployeeProfilePicture', [EmployeeController::class, 'changeEmployeeProfilePicture'])->name('changeEmployeeProfilePicture');
      Route::post('addHrLocation', [EmployeeController::class, 'addHrLocation'])->name('addHrLocation');
      Route::delete('deleteHrLocation/{id}', [EmployeeController::class, 'deleteHrLocation'])->name('deleteHrLocation');
      Route::post('addOrUpdateBankAccount', [EmployeeController::class, 'addOrUpdateBankAccount'])->name('addOrUpdateBankAccount');
      Route::post('addOrUpdateLeaveCount', [EmployeeController::class, 'addOrUpdateLeaveCount'])->name('addOrUpdateLeaveCount');
      Route::post('addOrUpdateDocument', [EmployeeController::class, 'addOrUpdateDocument'])->name('addOrUpdateDocument');
      Route::get('getUserDocumentsAjax/{userId}', [EmployeeController::class, 'getUserDocumentsAjax'])->name('getUserDocumentsAjax');
      Route::get('downloadUserDocument/{userDocumentId}', [EmployeeController::class, 'downloadUserDocument'])->name('downloadUserDocument');
      Route::post('updateBasicInfo', [EmployeeController::class, 'updateBasicInfo'])->name('updateBasicInfo');
      Route::post('updateCompensationInfo', [EmployeeController::class, 'updateCompensationInfo'])->name('updateCompensationInfo');
      Route::post('addOrUpdateBankAccount', [EmployeeController::class, 'addOrUpdateBankAccount'])->name('addOrUpdateBankAccount');
      Route::post('updateWorkInformation', [EmployeeController::class, 'updateWorkInformation'])->name('updateWorkInformation');
      Route::post('updateEmergencyContactInfo', [EmployeeController::class, 'updateEmergencyContactInfo'])->name('updateEmergencyContactInfo');

      Route::post('addOrUpdatePayrollAdjustment', [EmployeeController::class, 'addOrUpdatePayrollAdjustment'])->name('addOrUpdatePayrollAdjustment');
      Route::delete('deletePayrollAdjustment/{id}', [EmployeeController::class, 'deletePayrollAdjustment'])->name('deletePayrollAdjustment');
      Route::get('getPayrollAdjustmentAjax/{id}', [EmployeeController::class, 'getPayrollAdjustmentAjax'])->name('getPayrollAdjustmentAjax');

      Route::get('getReportingToUsersAjax', [EmployeeController::class, 'getReportingToUsersAjax'])->name('getReportingToUsersAjax');
      Route::post('removeDevice', [EmployeeController::class, 'removeDevice'])->name('removeDevice');

      //Sales targets
      Route::post('addOrUpdateSalesTarget', [EmployeeController::class, 'addOrUpdateSalesTarget'])->name('addOrUpdateSalesTarget');
      Route::delete('destroySalesTarget/{id}', [EmployeeController::class, 'destroySalesTarget'])->name('destroySalesTarget');
      Route::get('getTargetByIdAjax/{id}', [EmployeeController::class, 'getTargetByIdAjax'])->name('getTargetByIdAjax');

      Route::post('toggleStatus/{id}', [EmployeeController::class, 'toggleStatus'])->name('employees.toggleStatus');
      Route::post('relieve/{id}', [EmployeeController::class, 'relieveEmployee'])->name('employees.relieve');
      Route::post('retire/{id}', [EmployeeController::class, 'retireEmployee'])->name('employees.retire');
    });


    Route::prefix('account/')->name('account.')->group(function () {
      Route::get('/', [AccountController::class, 'index'])->name('index');
      Route::get('activeInactiveUserAjax/{id}', [AccountController::class, 'activeInactiveUserAjax'])->name('activeInactiveUserAjax');
      Route::get('suspendUserAjax/{id}', [AccountController::class, 'suspendUserAjax'])->name('suspendUserAjax');
      Route::get('deleteUserAjax/{id}', [AccountController::class, 'deleteUserAjax'])->name('deleteUserAjax');
      Route::get('viewUser/{id}', [AccountController::class, 'viewUser'])->name('viewUser');
      Route::get('indexAjax', [AccountController::class, 'userListAjax'])->name('userListAjax');
      Route::delete('deleteUserAjax/{id}', [AccountController::class, 'deleteUserAjax'])->name('deleteUserAjax');
      Route::get('getRolesAjax', [AccountController::class, 'getRolesAjax'])->name('getRolesAjax');
      Route::get('getUsersAjax', [AccountController::class, 'getUsersAjax'])->name('getUsersAjax');
      Route::get('getUsersByRoleAjax/{role}', [AccountController::class, 'getUsersByRoleAjax'])->name('getUsersByRoleAjax');
      Route::post('addOrUpdateUserAjax', [AccountController::class, 'addOrUpdateUserAjax'])->name('addOrUpdateUserAjax');
      Route::get('editUserAjax/{id}', [AccountController::class, 'editUserAjax'])->name('editUserAjax');
      Route::post('updateUserAjax/{id}', [AccountController::class, 'updateUserAjax'])->name('updateUserAjax');
      Route::post('updateUserStatusAjax/{id}', [AccountController::class, 'updateUserStatusAjax'])->name('updateUserStatusAjax');
      Route::post('changeUserStatusAjax/{id}', [AccountController::class, 'changeUserStatusAjax'])->name('changeUserStatusAjax');
      Route::post('changePassword', [AccountController::class, 'changePassword'])->name('changePassword');
    });

    //Audit Logs
    Route::prefix('auditLogs/')->name('auditLogs.')->group(function () {
      Route::get('/', [AuditLogController::class, 'index'])->name('index');
      Route::get('show/{id}', [AuditLogController::class, 'show'])->name('show');
    });

    //Leave Types
    Route::prefix('leaveTypes/')->name('leaveTypes.')->group(function () {
      Route::get('', [LeaveTypeController::class, 'index'])->name('index');
      Route::get('getLeaveTypesAjax', [LeaveTypeController::class, 'getLeaveTypesAjax'])->name('getLeaveTypesAjax');
      Route::post('addOrUpdateLeaveTypeAjax', [LeaveTypeController::class, 'addOrUpdateLeaveTypeAjax'])->name('addOrUpdateLeaveTypeAjax');
      Route::get('getLeaveTypeAjax/{id}', [LeaveTypeController::class, 'getLeaveTypeAjax'])->name('getLeaveTypeAjax');
      Route::get('getCodeAjax', [LeaveTypeController::class, 'getCodeAjax'])->name('getCodeAjax');
      Route::delete('deleteLeaveTypeAjax/{id}', [LeaveTypeController::class, 'deleteLeaveTypeAjax'])->name('deleteLeaveTypeAjax');
      Route::post('changeStatus/{id}', [LeaveTypeController::class, 'changeStatus'])->name('changeStatus');
      Route::get('checkCodeValidationAjax', [LeaveTypeController::class, 'checkCodeValidationAjax'])->name('checkCodeValidationAjax');
    });


    //Expense Types
    Route::prefix('expenseTypes/')->name('expenseTypes.')->group(function () {
      Route::get('/', [ExpenseTypeController::class, 'index'])->name('index');
      Route::get('getExpenseTypesListAjax', [ExpenseTypeController::class, 'getExpenseTypesListAjax'])->name('getExpenseTypesListAjax');
      Route::post('addOrUpdateExpenseTypeAjax', [ExpenseTypeController::class, 'addOrUpdateExpenseTypeAjax'])->name('addOrUpdateAjax');
      Route::get('getExpenseTypeAjax/{id}', [ExpenseTypeController::class, 'getExpenseTypeAjax'])->name('getExpenseTypeAjax');
      Route::delete('deleteExpenseTypeAjax/{id}', [ExpenseTypeController::class, 'deleteExpenseTypeAjax'])->name('deleteExpenseTypeAjax');
      Route::post('changeStatus/{id}', [ExpenseTypeController::class, 'changeStatus'])->name('changeStatus');
      Route::get('getCodeAjax', [ExpenseTypeController::class, 'getCodeAjax'])->name('getCodeAjax');
      Route::get('view/{id}', [ExpenseTypeController::class, 'view'])->name('view');
      Route::post('addOrUpdateRule', [ExpenseTypeController::class, 'addOrUpdateRule'])->name('addOrUpdateRule');
      Route::delete('deleteRule/{id}', [ExpenseTypeController::class, 'deleteRule'])->name('deleteRule');
      Route::get('checkCodeValidationAjax', [ExpenseTypeController::class, 'checkCodeValidationAjax'])->name('checkCodeValidationAjax');
    });


    //Teams
    Route::prefix('teams/')->name('teams.')->group(function () {
      Route::get('', [TeamController::class, 'index'])->name('index');
      Route::get('getTeamsListAjax', [TeamController::class, 'getTeamsListAjax'])->name('getTeamsListAjax');
      Route::post('addOrUpdateTeamAjax', [TeamController::class, 'addOrUpdateTeamAjax'])->name('addOrUpdateTeamAjax');
      Route::get('getTeamAjax/{id}', [TeamController::class, 'getTeamAjax'])->name('getTeamAjax');
      Route::delete('deleteTeamAjax/{id}', [TeamController::class, 'deleteTeamAjax'])->name('deleteTeamAjax');
      Route::post('changeStatus/{id}', [TeamController::class, 'changeStatus'])->name('changeStatus');
      Route::get('getCodeAjax', [TeamController::class, 'getCodeAjax'])->name('getCodeAjax');
      Route::get('getTeamListAjax', [TeamController::class, 'getTeamListAjax'])->name('getTeamListAjax');
      Route::get('checkCodeValidationAjax', [TeamController::class, 'checkCodeValidationAjax'])->name('checkCodeValidationAjax');
    });


    //Shifts
    Route::prefix('shifts/')->name('shifts.')->group(function () {
      Route::get('', [ShiftController::class, 'index'])->name('index');
      Route::get('getShiftsListAjax', [ShiftController::class, 'getShiftsListAjax'])->name('getShiftsListAjax');
      Route::post('addOrUpdateShiftAjax', [ShiftController::class, 'addOrUpdateShiftAjax'])->name('addOrUpdateShiftAjax');
      Route::get('getCodeAjax', [ShiftController::class, 'getCodeAjax'])->name('getCodeAjax');
      Route::get('getShiftAjax/{id}', [ShiftController::class, 'getShiftAjax'])->name('getShiftAjax');
      Route::delete('deleteShiftAjax/{id}', [ShiftController::class, 'deleteShiftAjax'])->name('deleteShiftAjax');
      Route::post('changeStatus/{id}', [ShiftController::class, 'changeStatus'])->name('changeStatus');
      Route::get('getShiftListAjax', [ShiftController::class, 'getShiftListAjax'])->name('getShiftListAjax');
      Route::get('checkCodeValidationAjax', [ShiftController::class, 'checkCodeValidationAjax'])->name('checkCodeValidationAjax');
    });

    //Visits
    Route::group(['prefix' => 'visits'], function () {
      Route::get('/', [VisitController::class, 'index'])->name('visits.index');
      Route::get('/getListAjax', [VisitController::class, 'getListAjax'])->name('visits.getListAjax');
      Route::delete('/deleteVisitAjax/{id}', [VisitController::class, 'deleteVisitAjax'])->name('visits.deleteVisitAjax');
      Route::get('/getByIdAjax/{id}', [VisitController::class, 'getByIdAjax'])->name('visits.getByIdAjax');
    });

    //Leave Requests
    Route::group(['prefix' => 'leaveRequests'], function () {
      Route::get('/', [LeaveController::class, 'index'])->name('leaveRequests.index');
      Route::get('/getListAjax', [LeaveController::class, 'getListAjax'])->name('leaveRequests.getListAjax');
      Route::post('/actionAjax', [LeaveController::class, 'actionAjax'])->name('leaveRequests.actionAjax');
      Route::get('/getByIdAjax/{id}', [LeaveController::class, 'getByIdAjax'])->name('leaveRequests.getByIdAjax');
    });

    //Clients
    Route::prefix('clients/')->name('client.')->group(function () {
      Route::get('', [ClientController::class, 'index'])->name('index');
      Route::get('show/{id}', [ClientController::class, 'show'])->name('show');
      Route::get('create', [ClientController::class, 'create'])->name('create');
      Route::post('store', [ClientController::class, 'store'])->name('store');
      Route::get('edit/{id}', [Clientcontroller::class, 'edit'])->name('edit');
      Route::post('update/{id}', [Clientcontroller::class, 'update'])->name('update');
      Route::post('changeStatus', [Clientcontroller::class, 'changeStatus'])->name('changeStatus');
      Route::delete('destroy/{id}', [Clientcontroller::class, 'destroy'])->name('destroy');
    });

    //Employees
    Route::get('employee/getGeofenceGroups', [EmployeeController::class, 'getGeofenceGroups'])->name('employee.getGeofenceGroups');
    Route::get('employee/getIpGroups', [EmployeeController::class, 'getIpGroups'])->name('employee.getIpGroups');
    Route::get('employee/getQrGroups', [EmployeeController::class, 'getQrGroups'])->name('employee.getQrGroups');
    Route::get('employee/getSites', [EmployeeController::class, 'getSites'])->name('employee.getSites');
    Route::get('employee/getDynamicQrDevices', [EmployeeController::class, 'getDynamicQrDevices'])->name('employee.getDynamicQrDevices');

    Route::get('employee/myProfile', [EmployeeController::class, 'myProfile'])->name('employee.myProfile');
  });

  //Expense Requests
  Route::group(['prefix' => 'expenseRequests'], function () {
    Route::get('/', [ExpenseController::class, 'index'])->name('expenseRequests.index');
    Route::get('/indexAjax', [ExpenseController::class, 'indexAjax'])->name('expenseRequests.indexAjax');
    Route::get('/getByIdAjax/{id}', [ExpenseController::class, 'getByIdAjax'])->name('expenseRequests.getByIdAjax');
    Route::post('/actionAjax', [ExpenseController::class, 'actionAjax'])->name('expenseRequests.actionAjax');
  });

  //Departments
  Route::group(['prefix' => 'departments'], function () {
    Route::get('/', [DepartmentsController::class, 'index'])->name('departments.index');
    Route::get('/indexAjax', [DepartmentsController::class, 'indexAjax'])->name('departments.indexAjax');
    Route::post('/addOrUpdateDepartmentAjax', [DepartmentsController::class, 'addOrUpdateDepartmentAjax'])->name('departments.addOrUpdateDepartmentAjax');
    Route::get('/getListAjax', [DepartmentsController::class, 'getListAjax'])->name('departments.getListAjax');
    Route::get('/getParentDepartments', [DepartmentsController::class, 'getParentDepartments'])->name('departments.getParentDepartments');
    Route::get('/getDepartmentAjax/{id}', [DepartmentsController::class, 'getDepartmentAjax'])->name('departments.getDepartmentAjax');
    Route::delete('/deleteAjax/{id}', [DepartmentsController::class, 'deleteAjax'])->name('departments.deleteAjax');
    Route::post('/changeStatus/{id}', [DepartmentsController::class, 'changeStatus'])->name('departments.changeStatus');
  });

  //Designations
  Route::group(['prefix' => 'designations'], function () {
    Route::get('/', [DesignationController::class, 'index'])->name('designations.index');
    Route::get('/indexAjax', [DesignationController::class, 'indexAjax'])->name('designations.indexAjax');
    Route::get('/getDesignationListAjax', [DesignationController::class, 'getDesignationListAjax'])->name('getDesignationListAjax');
    Route::post('/addOrUpdateAjax', [DesignationController::class, 'addOrUpdateAjax'])->name('designations.addOrUpdateAjax');
    Route::get('/getByIdAjax/{id}', [DesignationController::class, 'getByIdAjax'])->name('designations.getByIdAjax');
    Route::delete('/deleteAjax/{id}', [DesignationController::class, 'deleteAjax'])->name('designations.deleteAjax');
    Route::post('/changeStatus/{id}', [DesignationController::class, 'changeStatus'])->name('designations.changeStatus');
    Route::get('/checkCodeValidationAjax', [DesignationController::class, 'checkCodeValidationAjax'])->name('designations.checkCodeValidationAjax');
  });

  // Device Status
  Route::group(['prefix' => 'device'], function () {
    Route::get('/', [DeviceController::class, 'index'])->name('device.index');
    Route::get('/indexAjax', [DeviceController::class, 'indexAjax'])->name('device.indexAjax');
    Route::get('/getByIdAjax/{id}', [DeviceController::class, 'getByIdAjax'])->name('device.getByIdAjax');
    Route::delete('/deleteAjax/{id}', [DeviceController::class, 'deleteAjax'])->name('device.deleteAjax');
  });

  //Organization Hierarchy
  Route::group(['prefix' => 'organizationHierarchy'], function () {
    Route::get('/', [OrganisationHierarchyController::class, 'index'])->name('organizationHierarchy.index');
  });


   //SuperAdmin Api

   Route::post('/superadmin/subscriptions/update-status/{id}', [SuperAdminController::class, 'updateStatus'])->middleware(SuperAdminMiddleware::class)->name('superadmin.subscriptions.updateStatus');
   Route::post('/superadmin/subscriptions/update-dates/{id}', [SuperAdminController::class, 'updateDates'])->middleware(SuperAdminMiddleware::class)->name('superadmin.subscriptions.updateDates');
   // Toggle user status
   Route::post('/superadmin/users/toggle-status/{id}', [SuperAdminController::class, 'toggleStatus'])->middleware(SuperAdminMiddleware::class)->name('superadmin.users.toggleStatus');
   
   // Reset password
   Route::post('/superadmin/users/reset-password/{id}', [SuperAdminController::class, 'resetPassword'])->middleware(SuperAdminMiddleware::class)->name('superadmin.users.resetPassword');
   
    // Toggle business status
   Route::post('/superadmin/businesses/toggle-status/{id}', [SuperAdminController::class, 'toggleBusinessStatus'])->middleware(SuperAdminMiddleware::class)->name('superadmin.businesses.toggleStatus');
   // Add subscription to a business
   Route::post('/superadmin/add-subscription', [SuperAdminController::class, 'addSubscription'])->middleware(SuperAdminMiddleware::class)
   ->name('superadmin.businesses.addSubscription');
   Route::get('/superadmin/dashboard', [SuperAdminController::class, 'getDashboardData'])->middleware(SuperAdminMiddleware::class);
   Route::get('/superadmin/monthly-subscription-data', [SuperAdminController::class, 'getMonthlySubscriptionData'])->middleware(SuperAdminMiddleware::class);
   Route::post('/superadmin/businesses-add-subscription/{businessId}', [SuperAdminController::class, 'addBusinessSubscription'])->middleware(SuperAdminMiddleware::class)->name('superadmin.businesses.addSubscriptionOld');
   
   Route::get('/superadmin', [SuperAdminController::class, 'index'])
       ->middleware(SuperAdminMiddleware::class)
       ->name('superadmin.index');
   Route::get('/superadmin/packages', [SuperAdminController::class, 'packages'])->middleware(SuperAdminMiddleware::class)->name('superadmin.packages');
       // Add the package
   Route::get('superadmin/packages/add', [SuperAdminController::class, 'addPackages'])->middleware(SuperAdminMiddleware::class)->name('superadmin.packages.add');
   // Store the new package at URL superadmin/packages/store
   Route::post('superadmin/packages/store', [SuperAdminController::class, 'storePackage'])->middleware(SuperAdminMiddleware::class)->name('superadmin.packages.store');
   // Edit the package
   Route::get('superadmin/packages/edit/{id}', [SuperAdminController::class, 'editPackages'])->middleware(SuperAdminMiddleware::class)->name('superadmin.packages.edit');
   Route::put('superadmin/packages/update/{id}', [SuperAdminController::class, 'updatePackages'])->middleware(SuperAdminMiddleware::class)->name('superadmin.packages.update');
   // Delete the package
   Route::delete('superadmin/packages/delete/{id}', [SuperAdminController::class, 'deletePackages'])->middleware(SuperAdminMiddleware::class)->name('superadmin.packages.delete');
   // In routes/web.php
   Route::get('/superadmin/subscriptions', [SuperAdminController::class, 'showSubscriptions'])->middleware(SuperAdminMiddleware::class)->name('superadmin.subscriptions');
  


    Route::get('businesses', [SuperAdminController::class, 'businesses'])->middleware(SuperAdminMiddleware::class)->name('superadmin.businesses');
    Route::get('businesses/add', [SuperAdminController::class, 'addBusiness'])->middleware(SuperAdminMiddleware::class)->name('superadmin.businesses.add');
    Route::post('businesses/store', [SuperAdminController::class, 'storeBusiness'])->middleware(SuperAdminMiddleware::class)->name('superadmin.businesses.store');
    Route::get('businesses/edit/{id}', [SuperAdminController::class, 'editBusiness'])->middleware(SuperAdminMiddleware::class)->name('superadmin.businesses.edit');
    Route::get('businesses/show/{id}', [SuperAdminController::class, 'showBusiness'])->middleware(SuperAdminMiddleware::class)->name('superadmin.businesses.show');
    Route::post('businesses/update/{id}', [SuperAdminController::class, 'updateBusiness'])->middleware(SuperAdminMiddleware::class)->name('superadmin.businesses.update');
    Route::delete('businesses/delete/{id}', [SuperAdminController::class, 'deleteBusiness'])->middleware(SuperAdminMiddleware::class)->name('superadmin.businesses.delete');
    
    Route::post('settings/updateBasicSettings', [SuperAdminController::class, 'updateBasicSettings'])->middleware(SuperAdminMiddleware::class)->name('settings.updateBasicSettings');
    Route::post('settings/updateDashboardSettings', [SuperAdminController::class, 'updateDashboardSettings'])->middleware(SuperAdminMiddleware::class)->name('settings.updateDashboardSettings');
    Route::post('settings/updateMobileAppSettings', [SuperAdminController::class, 'updateMobileAppSettings'])->middleware(SuperAdminMiddleware::class)->name('settings.updateMobileAppSettings');
    Route::post('settings/updateMapSettings', [SuperAdminController::class, 'updateMapSettings'])->middleware(SuperAdminMiddleware::class)->name('settings.updateMapSettings');




    Route::get('/superadmin/settings', [SuperAdminController::class, 'superAdminSettings'])->middleware(SuperAdminMiddleware::class)->name('superadmin.settings');
    Route::get('/superadmin/communicator', [SuperAdminController::class, 'superAdminCommunicator'])->middleware(SuperAdminMiddleware::class)->name('superadmin.communicator');
    Route::post('/superadmin/communicator/store', [SuperAdminController::class, 'sendMessage'])->middleware(SuperAdminMiddleware::class)->name('superadmin.communicator.store');
    Route::post('/superadmin/updatePaymentSettings', [SuperAdminController::class, 'updatePaymentSettings'])->middleware(SuperAdminMiddleware::class)->name('superadmin.pay.settings');
    
    Route::get('/check_subcription', [SuperAdminController::class, 'checkSubcription'])->name('check.subcription');
    Route::post('/update-subscription', [SuperAdminController::class, 'updateSubscription'])->name('update.subscription');



});
