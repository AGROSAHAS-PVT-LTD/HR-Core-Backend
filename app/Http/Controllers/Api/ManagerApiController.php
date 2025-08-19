<?php

// namespace Modules\ManagerApp\App\Http\Controllers\Api;
namespace App\Http\Controllers\Api;



use App\Api\Shared\Responses\Error;
use App\Api\Shared\Responses\Success;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ExpenseRequest;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\Subscription;
use App\Models\Package;
use App\Models\Business;
use App\Models\Role;
use App\Models\Shift;
use App\Models\Client;
use App\Models\Team;
use Carbon\Carbon;
use Constants;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Support\Str;
use App\Mail\BusinessCreatedMail;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;


class ManagerApiController extends Controller
{
    
    public function loginWithEmailAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
             'email' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Error::response($validator->messages());
        }

        $user = User::where('email', $request->email)->with('roles')->first();
        if (!$user) {
            return response()->json([
                'statusCode' => 604,
                'status' => 'error',
                'message' => 'User not found.',
                'data' => 'User not found. register an account now.'
            ], 200); 
            return Error::response('User not found.');
        }
        $userRole = $user->roles()->first();
        
        if (isset($request['isManager']) && $request->input('isManager')) {
            // If isManager is true, check for Admin role
            if ($userRole == null || $userRole->name != 'admin') {
                return response()->json([
                'statusCode' => 506,
                'status' => 'error',
                'message' => 'You are not authorized to login. Only accessed by Business owners',
                'data' => 'You are not authorized to login.  Only accessed by Business owners'
            ], 200); 
            }
        }
        else {
             return response()->json([
                'statusCode' => 506,
                'status' => 'error',
                'message' => 'You are not authorized to login. Only accessed by employees',
                'data' => 'You are not authorized to login. Only accessed by employees'
            ], 200); 
        }
        
        if ($user->status == 'inactive') {
            return response()->json([
                'statusCode' => 506,
                'status' => 'error',
                'message' => 'User is inactive.',
                'data' => 'User is inactive.'
            ], 200); 
        }

        try {
           // Set TTL for one year (365 days)
            JWTAuth::factory()->setTTL(365 * 24 * 60); // 365 days in minutes
            // Create a JWT token with custom expiration
            $token = JWTAuth::fromUser($user, [
                'exp' => Carbon::now()->addDays(365)->timestamp // Expiration set to one year
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'statusCode' => 506,
                'status' => 'error',
                'message' => 'could_not_create_token error => ' . $e->getMessage(),
                'data' => 'could_not_create_token error => ' . $e->getMessage()
            ], 200);
            return response()->json(['error' => 'could_not_create_token error => ' . $e->getMessage()], 500);
        }

        $currentSubscription = null;
        
        if($user){
            // Fetch current subscription
            $currentSubscription = Subscription::where('business_id', $user->business_id )
            ->where(function ($query) {
                $query->where('status', 'approved')
                      ->orWhere('end_date', '>=', now());
            })
            ->latest('end_date')
            ->first();
        }

        $response = [
            'token' => $token,
            'id' => $user->id,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'emailId' => $user->email,
            'employeeId' => $user->user_name,
            'phoneNumber' => $user->phone,
            'Gender' => 'male',
            'Avatar' => $user->profile_picture ?? '',
            'status' => $user->status,
            'subscription_status' => $currentSubscription->isActive(),
        ];
        return response()->json([
            'statusCode' => 200,
            'status' => 'success',
            'data' => $response
        ], 200); 

    }
    
    public function getPackages()
    {
        $user = auth()->user();
        $currentSubscription = null;
        
        if($user){
            // Fetch current subscription
            $currentSubscription = Subscription::where('business_id', $user->business_id )
            ->where(function ($query) {
                $query->where('status', 'approved')
                      ->orWhere('end_date', '>=', now());
            })
            ->latest('end_date')
            ->first();
        }
        
        
    
        // Fetch active packages
        $packages = Package::where('is_active', true)->get();
    
        // Prepare subscription data safely
        $currentSubscriptionData = $currentSubscription ? [
            'name' => $currentSubscription->package->name,
            'id' => $currentSubscription->id,
            'user_count' => $currentSubscription->package->user_count ?? null,
            'price' => $currentSubscription->package_price,
            'remaining_days' => $currentSubscription->remaining_days ?? null,
            'end_date' => $currentSubscription->end_date,
            'status' => $currentSubscription->isActive(),
            'is_active' => $currentSubscription->isActive(),
            'start_date' => $currentSubscription->start_date,
        ] : null;
    
        return response()->json([
            'statusCode' => 200,
            'status' => 'success',
            'data' => $currentSubscriptionData,
            'packages' => $packages,
        ]);
    }

    public function getUnAuthPackages()
    {
    
        // Fetch active packages
        $packages = Package::where('is_active', true)->get();
        return response()->json([
            'statusCode' => 200,
            'status' => 'success',
            'data' => null,
            'packages' => $packages,
        ]);
    }
    
    public function getUserData()
    {
        $user = auth()->user();
        $Users = User::where('business_id', $user->business_id)
             ->orderBy('created_at', 'desc') // Order by creation date in descending order
             ->get();
             
        return response()->json([
            'statusCode' => 200,
            'status' => 'success',
            'data' => $Users,
            ]);
    }
    
    
    public function addNewUserData(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'user_name' => 'nullable|string|max:255|unique:users,user_name',
            'email' => 'required|email|unique:users,email',
            'gender' => 'required|string|max:255',
            // 'designation' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6'
        ]);
    
        // Handle validation failure
        if ($validator->fails()) {
            return Error::response($validator->messages());
        }
       
        // Get authenticated user
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'statusCode' => 401,
                'status' => 'error',
                'message' => 'Unauthorized access. Please log in.',
            ], 401);
        }
    
        // Prepare validated data
        $validatedUserData = $validator->validated();
        $businessPrefix = $user->business_id ?? '0';
    
        try {
            // Start Transaction
            DB::beginTransaction();
    
            // Register and activate the new user
            $createdUser = Sentinel::registerAndActivate([
                'first_name' => $validatedUserData['first_name'],
                'last_name' => $validatedUserData['last_name'],
                'email' => $validatedUserData['email'],
                'password' => $validatedUserData['password'],
            ]);
    
            // Update additional user details
            $createdUser->business_id = $businessPrefix;
            $createdUser->phone = $validatedUserData['phone_number'];
            $createdUser->designation = $validatedUserData['designation'];
            $createdUser->gender = $validatedUserData['gender'];
            $createdUser->status = 'active';
            $createdUser->user_name = $businessPrefix . '_' . $validatedUserData['user_name'];
            $createdUser->save();
    
            // Attach role to the new user
            $user->assignRole('field_employee');

    
            $businessId = $createdUser->business_id;
    
                // Create Default Team if not exists
            $team = Team::firstOrCreate(
                ['name' => 'Default Team', 'business_id' => $businessId],
                [
                    'description' => 'This is the default team.',
                    'status' => 'active',
                    'is_chat_enabled' => true,
                    'created_by_id' => $createdUser->id,
                    'updated_by_id' => $createdUser->id,
                    'business_id' => $businessId,
                ]
            );
    
            // Create Default Shift if not exists
            $shift =  Shift::firstOrCreate(
                ['title' => 'Default Shift', 'business_id' => $businessId],
                [
                    'description' => 'This is the default shift.',
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'status' => 'active',
                    'sunday' => false,
                    'monday' => true,
                    'tuesday' => true,
                    'wednesday' => true,
                    'thursday' => true,
                    'friday' => true,
                    'saturday' => false,
                    'is_site_specific' => false,
                    'created_by_id' => $createdUser->id,
                    'updated_by_id' => $createdUser->id,
                    'business_id' => $businessId,
                ]
            );
            $createdUser->shift_id = $shift->id;
            $createdUser->team_id = $team->id;
            $createdUser->save();
              
            // Commit the Transaction
            DB::commit();
            
            // Get business name safely
            $businessName = optional($user->business)->name ?? 'Your Business';
    
            // Send Welcome Email
            Mail::to($createdUser->email)->send(new WelcomeUserMail(
                $createdUser,
                $validatedUserData['password'],
                $businessName
            ));
    
            return Success::response('User account created successfully');
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
    
            // Enhanced Logging
            \Log::error('User creation failed: ' . $e->getMessage(), [
                'user_email' => $validatedUserData['email'],
                'business_id' => $user->business_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'statusCode' => 500,
                'status' => 'error',
                'message' => 'Failed to create user account. Please try again later.',
            ], 500);
        }
    }
    

    public function addNewAccountData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'business_name' => 'required|string|max:255',
            'plan_type'     => 'required|string|max:255',
            'phone_number'  => 'required|string|max:20|unique:users,phone',
            'password'      => 'required|string|min:8',
            'package'       => 'required|exists:packages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 422,
                'status'     => 'error',
                'errors'     => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            // 1. Create Business
            $business = Business::create([
                'name'       => $validated['business_name'],
                'email'      => $validated['email'],
                'contact'    => $validated['phone_number'],
                'start_date' => now(),
                'created_by' => auth()->id(), // optional if token-based auth
            ]);

            // 2. Create Owner User
            $owner = User::create([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'email'      => $validated['email'],
                'user_name'  => $business->id . '_' . Str::slug($validated['first_name'].$validated['last_name']),
                'phone'      => $validated['phone_number'],
                'phone_verified_at' => now(),
                'password'   => bcrypt($validated['password']),
                'email_verified_at' => now(),
                'code'       => 'GPS' . rand(100000, 999999),
                'business_id'=> $business->id,
            ]);

            // Assign admin role
            $owner->assignRole('admin');

            // 3. Update Business with owner + package
            $business->owner_id   = $owner->id;
            $business->package_id = $validated['package'];
            $business->save();

            // 4. Create Subscription
            $package = Package::findOrFail($validated['package']);
            $price   = ($validated['plan_type'] === 'yearly') ? $package->yearly_price : $package->price;
            $endDate = ($validated['plan_type'] === 'yearly')
                ? now()->addYear()
                : now()->add($package->interval_count, $package->interval);

            Subscription::create([
                'user_id'        => $owner->id,
                'package_id'     => $package->id,
                'business_id'    => $business->id,
                'start_date'     => now(),
                'end_date'       => $endDate,
                'trial_end_date' => $package->trial_days ? now()->addDays($package->trial_days) : null,
                'original_price' => $price,
                'package_price'  => $price,
                'package_details'=> $package->details,
            ]);

            DB::commit();
            // Send email after commit
            Mail::to($owner->email)->queue(new BusinessCreatedMail($owner, $business, $validated['password']));

            return response()->json([
                'statusCode' => 200,
                'status'     => 'success',
                'data'       => [
                    'trial_days'  => $package->trial_days,
                    'business_id' => $business->id,
                    'user_id'     => $owner->id,
                ],
                'message' => "User account and business created successfully",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());

            return response()->json([
                'statusCode' => 500,
                'status' => 'error',
                'message' => 'Failed to create business and owner. '.$e->getMessage(),
            ], 500);
        }
    }

    
    public function editUserData(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|int|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user_id,
            'gender' => 'required|string|in:male,female,other',
            // 'designation' => 'required|string|max:255',
            'status' => 'required|string|max:25',
            'phone_number' => 'required|string|max:20|unique:users,phone,' . $request->user_id,
            'password' => 'nullable|string|min:6'
        ]);
    
        $validatedUserData = $validator->validated();
        try {
            // Fetch the user
            $user = User::findOrFail($validatedUserData['user_id']);
    
            // Update user details
            $user->first_name = $validatedUserData['first_name'];
            $user->last_name = $validatedUserData['last_name'];
            $user->email = $validatedUserData['email'];
            $user->status = $validatedUserData['status'];
            $user->gender = $validatedUserData['gender'];
            // $user->designation = $validatedUserData['designation'];
            $user->phone = $validatedUserData['phone_number'];
            

            if (!empty($validatedUserData['password'])){
                $user->password = bcrypt($validatedUserData['password']);
            }
            $user->save();
            
            return Success::response('User account updated successfully');
    
        } catch (\Exception $e) {
            return Error::response('Failed to update user data '. $e);
        }
    }
    
    public function getDashboard()
    {
        $now = now();

        // $totalUserIds = User::where('parent_id', auth()->user()->id)
        $totalUserIds = User::where('business_id', auth()->user()->business_id)
            ->where('status', 'active')
            ->select('id');

        $totalUsersCount = $totalUserIds->count();

        $userAttendances = Attendance::whereIn('user_id', $totalUserIds)
            ->with('user')
            ->whereDate('created_at', $now->toDateString())
            ->get();

        $userDevices = UserDevice::whereIn('user_id', $totalUserIds)
            ->get();

        $users = [];

        foreach ($userAttendances as $attendance) {
            $userDevice = $userDevices->where('user_id', $attendance->user_id)->first();
            if (!$userDevice) {
                continue;
            }

            $users[] = [
                'employeeId' => $attendance->user->id,
                'name' => $attendance->user->getFullName(),
                'checkedInTime' => date('h:i A',strtotime($attendance->created_at)),
                'checkOutTime' => $attendance->check_out_time ? date('h:i A',strtotime($attendance->check_out_time)) : null,
                'device' => [
                    'address' => $userDevice->address,
                    'deviceType' => $userDevice->device_type,
                    'batteryPercentage' => $userDevice->battery_percentage,
                    'brand' => $userDevice->brand,
                    'isGPSOn' => boolval($userDevice->is_gps_on),
                    'isWifiOn' => boolval($userDevice->is_wifi_on),
                    'latitude' => floatval($userDevice->latitude),
                    'longitude' => floatval($userDevice->longitude),
                    'model' => $userDevice->model,
                    'lastUpdatedTime' => date('h:i A',strtotime($userDevice->updated_at)),
                ]
            ];
        }

        $currentSubscription = Subscription::where('business_id', auth()->user()->business_id )
            ->where(function ($query) {
                $query->where('status', 'approved')
                      ->orWhere('end_date', '>=', now());
            })
            ->latest('end_date')
            ->first();
            
        $onLeaveUsersCount = LeaveRequest::where('status', 'approved')
            ->whereDate('from_date', '<=', $now->toDateString())
            ->whereDate('to_date', '>=', $now->toDateString())
            ->whereIn('user_id', $totalUserIds)
            ->count();

        $checkedInUsersCount = $userAttendances->where('status', 'checked_in')->count();


        $response = [
            'users' => $users,
            'totalUsersCount' => $totalUserIds->count(),
            'checkedInUsersCount' => $checkedInUsersCount,
            'checkedOutUsersCount' => $userAttendances->where('status', 'checked_out')->count(),
            'checkInPendingUsersCount' => $totalUsersCount - $checkedInUsersCount - $onLeaveUsersCount,
            'absentUsersCount' => $totalUsersCount - $userAttendances->count() - $onLeaveUsersCount,
            'onLeaveUsersCount' => $onLeaveUsersCount,
            'subscription_status' => $currentSubscription->isActive(),
        ];
        
         return response()->json([
            'statusCode' => 200,
            'status' => 'success',
            'data' => $response,
            ]);
        

        return Success::response($response);
    }

    public function getEmployeesStatusOld()
    {
        $now = now();
        // $totalUserIds = User::where('parent_id', auth()->user()->id)
        $totalUserIds = User::where('business_id', auth()->user()->business_id)
            ->where('status', 'active')
            ->select('id');

        $userAttendances = Attendance::whereIn('user_id', $totalUserIds)
            ->with('user')
            ->whereDate('created_at', $now->toDateString())
            ->get();

        $userDevices = UserDevice::whereIn('user_id', $userAttendances->pluck('user_id'))
            ->get();

        $users = [];

        foreach ($userAttendances as $attendance) {

            $userDevice = $userDevices->where('user_id', $attendance->user_id)->first();

            if (!$userDevice) {
                continue;
            }

            $users[] = [
                'employeeId' => $attendance->user->id,
                'name' => $attendance->user->getFullName(),
                'checkedInTime' => date('h:i A', strtotime($attendance->created_at)),
                'checkOutTime' => $attendance->check_out_time ? date('h:i A',strtotime($attendance->check_out_time)): null,
                'status' => $attendance->status,
                'device' => [
                    'address' => $userDevice->address,
                    'deviceType' => $userDevice->device_type,
                    'batteryPercentage' => $userDevice->battery_percentage,
                    'brand' => $userDevice->brand,
                    'isGPSOn' => boolval($userDevice->is_gps_on),
                    'isWifiOn' => boolval($userDevice->is_wifi_on),
                    'latitude' => floatval($userDevice->latitude),
                    'longitude' => floatval($userDevice->longitude),
                    'model' => $userDevice->model,
                    'lastUpdatedTime' => date('h:i A',strtotime($userDevice->updated_at)),
                ]
            ];
        }
        return response()->json([
            'statusCode' => 200,
            'status' => 'success',
            'data' => $users,
            ]);
        

        return Success::response($users);
    }
    
    public function getEmployeesStatus()
    {
        try {
            $now = now();
            $totalUserIds = User::where('business_id', auth()->user()->business_id)
                ->where('status', 'active')
                ->select('id');
    
            $userAttendances = Attendance::whereIn('user_id', $totalUserIds)
                ->with('user')
                ->whereDate('created_at', $now->toDateString())
                ->get();
    
            $userDevices = UserDevice::whereIn('user_id', $userAttendances->pluck('user_id'))
                ->get();
    
            $users = [];
    
            foreach ($userAttendances as $attendance) {
                $userDevice = $userDevices->where('user_id', $attendance->user_id)->first();
    
                if (!$userDevice) {
                    continue;
                }
    
                $users[] = [
                    'employeeId' => $attendance->user->id,
                    'name' => $attendance->user->getFullName(),
                    'checkedInTime' => date('h:i A', strtotime($attendance->created_at)),
                    'checkOutTime' => $attendance->check_out_time ? date('h:i A', strtotime($attendance->check_out_time)) : null,
                    'status' => $attendance->status,
                    'device' => [
                        'address' => $userDevice->address,
                        'deviceType' => $userDevice->device_type,
                        'batteryPercentage' => $userDevice->battery_percentage,
                        'brand' => $userDevice->brand,
                        'isGPSOn' => boolval($userDevice->is_gps_on),
                        'isWifiOn' => boolval($userDevice->is_wifi_on),
                        'latitude' => floatval($userDevice->latitude),
                        'longitude' => floatval($userDevice->longitude),
                        'model' => $userDevice->model,
                        'lastUpdatedTime' => date('h:i A', strtotime($userDevice->updated_at)),
                    ]
                ];
            }
    
            return response()->json([
                'statusCode' => 200,
                'status' => 'success',
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            // Log the exception and return an error response
            \Log::error('Error in getEmployeesStatus: ' . $e->getMessage());
    
            return response()->json([
                'statusCode' => 500,
                'status' => 'error',
                'message' => 'An error occurred while fetching employee statuses. Please try again later.',
            ]);
        }
    }


    public function getAllLeaveRequests()
    {
        $now = now();
        // $totalUserIds = User::where('parent_id', auth()->user()->id)
        $totalUserIds = User::where('business_id', auth()->user()->business_id)
            ->where('status', 'active')
            ->select('id');

        $leaveRequests = LeaveRequest::whereIn('user_id', $totalUserIds)
            ->with('leaveType')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $response = [];

        foreach ($leaveRequests as $leaveRequest) {
            $response[] = [
                'id' => $leaveRequest->id,
                'employeeId' => $leaveRequest->user->id,
                'type' => $leaveRequest->leaveType->name,
                'employeeName' => $leaveRequest->user->getFullName(),
                'fromDate' => $leaveRequest->from_date,
                'toDate' => $leaveRequest->to_date,
                'status' => $leaveRequest->status,
                'reason' => $leaveRequest->remarks,
                'createdAt' => $leaveRequest->created_at->format(Constants::DateTimeFormat),
            ];
        }
        
        return response()->json([
            'statusCode' => 200,
            'status' => 'success',
            'data' => $response,
            ]);
        
        return Success::response($response);
    }

    public function changeLeaveStatus(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $remarks = $request->input('remarks');


        if ($id == null) {
            return Error::response('Leave request id is required');
        }

        if ($status == null) {
            return Error::response('Status is required');
        }

        $status = strtolower($status);

        if ($status != 'approved' && $status != 'rejected') {
            return Error::response('Invalid status');
        }

        $leaveRequest = LeaveRequest::find($id);

        if ($leaveRequest == null) {
            return Error::response('Leave request not found');
        }

        $leaveRequest->status = $status;
        $leaveRequest->approved_at = now();
        $leaveRequest->approved_by_id = auth()->user()->id;
        $leaveRequest->approver_remarks = $remarks;

        $leaveRequest->save();

        return Success::response('Leave request status updated successfully');
    }

    public function getAllExpenseRequests()
    {
        $totalUserIds = User::where('business_id', auth()->user()->id)
            ->where('status', 'active')
            ->select('id');

        $expenseRequests = ExpenseRequest::whereIn('user_id', $totalUserIds)
            ->with('expenseType')
            ->with('user')
            ->get();

        $response = $expenseRequests->map(function ($expenseRequest) {
            return [
                'id' => $expenseRequest->id,
                'employeeId' => $expenseRequest->user->id,
                'type' => $expenseRequest->expenseType->name,
                'employeeName' => $expenseRequest->user->getFullName(),
                'date' => Carbon::parse($expenseRequest->for_date)->format('Y-m-d'),
                'amount' => floatval($expenseRequest->amount),
                'approvedAmount' => floatval($expenseRequest->approved_amount),
                'status' => $expenseRequest->status,
            ];
        });
        
        return response()->json([
            'statusCode' => 200,
            'status' => 'success',
            'data' => $response,
            ]);
        

        return Success::response($response);
    }

    public function changeExpenseStatus(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $remarks = $request->input('remarks');
        $approvedAmount = $request->input('approvedAmount');

        if ($id == null) {
            return Error::response('Expense request id is required');
        }

        if ($status == null) {
            return Error::response('Status is required');
        }

        $status = strtolower($status);

        if ($status != 'approved' && $status != 'rejected') {
            return Error::response('Invalid status');
        }

        if ($status == 'approved' && $approvedAmount == null) {
            return Error::response('Approved amount is required');
        }

        if ($status == 'approved' && !is_numeric($approvedAmount)) {
            return Error::response('Approved amount should be a number');
        }

        $expenseRequest = ExpenseRequest::find($id);

        if ($expenseRequest == null) {
            return Error::response('Expense request not found');
        }

        $expenseRequest->status = $status;
        $expenseRequest->approved_at = now();
        $expenseRequest->approved_by_id = auth()->user()->id;
        $expenseRequest->approver_remarks = $remarks;
        $expenseRequest->approved_amount = $approvedAmount;

        $expenseRequest->save();

        return Success::response('Expense request status updated successfully');
    }
    
    public function getTaskDetails()
    {
        // Retrieve active users for the authenticated user's business
        $users = User::where('business_id', auth()->user()->business_id)
            ->where('status', 'active')
            ->get();
        
        // Retrieve active clients for the authenticated user's business
        $clients = Client::where('status', 1)
            ->where('business_id', auth()->user()->business_id)
            ->get();   
        
        // Return the response with status code and data
        return response()->json([
            'statusCode' => 200,
            'status' => 'success',
            'data' => [
                'clients' => $clients,
                'users' => $users,
            ],
        ]);
    }

}
