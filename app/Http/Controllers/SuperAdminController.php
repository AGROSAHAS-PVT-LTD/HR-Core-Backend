<?php

namespace App\Http\Controllers;

use App\Models\Package; // Import the Package model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Role;
use App\Models\Message;
use App\Models\Settings;
use Illuminate\Support\Facades\Hash;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Models\Business;
use Illuminate\Support\Facades\Log;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
        
    public function defaultHome(Request $request)
    {
        $packages = Package::latest()->paginate(10);
        // Pass packages data to the view
        return view('selfRegister.index', compact('packages'));
    }
    
    public function checkSubcription(Request $request)
    {
        $user = Auth::user();
        $settings = Settings::first();
        $business_id = $user->business_id;
        // Fetch the business details with related data
        $business = Business::with(['subscriptions', 'users'])->findOrFail($business_id);
        // Fetch the current or latest active subscription for the business
        $currentSubscription = Subscription::where('business_id', $business_id)
                            ->where(function($query) {
                                $query->where('status', 'approved')
                                      ->orWhere('end_date', '>=', now());
                            })
                            ->latest('end_date')
                            ->first();
                            
        // Pass packages data to the view
        return view('superadmin_saas.paySubscription', compact('business','currentSubscription','settings'));
    }
    
    public function defaultRegister(Request $request, $id)
    {
        $selectedPackage = Package::findOrFail($id);
        $packages = Package::where('is_active', true)->get();
        $paymentMethods = ['offline'];
        // Pass packages data to the view
        return view('selfRegister.register', compact('selectedPackage', 'packages', 'paymentMethods'));
    }

    public function defaultRegisterStore(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email',
            'contact' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'website' => 'nullable|url',
            // 'start_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users,user_name',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed', // password confirmation validation
        ]);
        
        // Validate Business data
        $validatedBusinessData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email',
            'contact' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'website' => 'nullable|url',
            'start_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        // Validate User (owner) data
        $validatedUserData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'user_name' => 'required|string|max:255|unique:users,user_name',
                'email' => 'required|email|unique:users,email',
                'phone_number' => 'required|string|max:20|unique:users,phone',
                'password' => 'required|string|min:8|confirmed', // password confirmation validation
        ]);
    
        // Add the currently authenticated user's ID as 'created_by'
        // $validatedBusinessData['created_by'] = Sentinel::getUser()->id;
        $validatedBusinessData['start_date'] = now();
    
        DB::beginTransaction();
    
        try {
            // First create the Business (so we can get its ID)
            $business = Business::create($validatedBusinessData);
        

            $owner = User::create([
                'user_name' => $validatedUserData['user_name'],
                'first_name' => $validatedUserData['first_name'],
                'last_name' => $validatedUserData['last_name'],
                'email' => $validatedUserData['email'],
                'phone' => $validatedUserData['phone_number'],
                'phone_verified_at' => now(),
                'password' => bcrypt($validatedUserData['password']),
                'code' => 'GPS' . rand(100000, 999999),
                'email_verified_at' => now(),
            ]);
            
            $owner->assignRole('admin');

            
            // Add owner_id and package_id to the Business data
            $business->owner_id = $owner->id;
            $business->package_id = $request->input('package');
            $business->save();
    
            // Handle file upload for the logo if provided
            if ($request->hasFile('logo')) {
                $business->logo = $request->file('logo')->store('logos', 'public');
                $business->save();
            }
    
            // Update the business_id in the user table
            $owner->business_id = $business->id;
            $owner->save();
    
            // Fetch the selected Package from the request
            $package = Package::findOrFail($request->input('package'));
    
            // Create a Subscription for the newly created business
            $subscriptionData = [
                'user_id' => $owner->id, // link the subscription to the owner
                'package_id' => $package->id, // Package ID from the selected Package
                'start_date' => now(), // Assuming the subscription starts now
                'end_date' => now()->add($package->interval_count, $package->interval), // Dynamically add subscription duration
                'business_id' => $business->id, // Link the subscription to the created business
                // 'payment_transaction_id' => $request->input('transaction_id'), // Transaction ID from the form
                // 'paid_via' => $request->input('paid_via'), // Payment method from the form
                'trial_end_date' => $package->trial_days ? now()->addDays($package->trial_days) : null, // Trial end date from the package's trial_days
                'original_price' => $package->price, // Price from the selected Package
                'package_price' => $package->price, // Package price from the selected Package
                'package_details' => $package->details, // Package details from the selected Package
            ];
            // Create the Subscription
            Subscription::create($subscriptionData);
            DB::commit();
    
            // Redirect with success message
            return redirect()->route('auth.login')->with('success', 'Business and owner created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create business and owner: ' . $e->getMessage()]);
        }
        
    }
    
    public function updatePaymentSettingsOld(Request $request)
    {
    
        // Validate the request
        $request->validate([
            'enable_offline_payment' => 'nullable|boolean',
            'offline_payment_details' => 'nullable|string',
            'stripe_pub_key' => 'nullable|string',
            'stripe_secret_key' => 'nullable|string',
            'paypal_mode' => 'required|in:live,sandbox',
            'paypal_client_id' => 'nullable|string',
            'paypal_app_secret' => 'nullable|string',
            'pesapal_consumer_key' => 'nullable|string',
            'pesapal_consumer_secret' => 'nullable|string',
            'pesapal_live' => 'nullable|boolean',
            'paystack_public_key' => 'nullable|string',
            'paystack_secret_key' => 'nullable|string',
            'flutterwave_public_key' => 'nullable|string',
            'flutterwave_secret_key' => 'nullable|string',
            'flutterwave_encryption_key' => 'nullable|string',
        ]);
    
        Log::info('Validation passed, preparing to update settings.');
    
        // Normalize boolean inputs
        $data = $request->all();
        $data['enable_offline_payment'] = $request->boolean('enable_offline_payment', false);
        $data['pesapal_live'] = $request->boolean('pesapal_live', false);
    
        try {
            // Attempt to update the settings
            Settings::first()->update($data);
            Log::info('Payment Settings Updated:', $data);
        } catch (\Exception $e) {
            // Log the error if update fails
            Log::error('Error updating payment settings: ' . $e->getMessage());
            // Optionally, you can redirect back with an error message
            return redirect()->back()->with('error', 'An error occurred while updating payment settings.');
        }
    
        return redirect()->back()->with('success', 'Payment settings updated successfully!');
    }

    public function updatePaymentSettings(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'enable_offline_payment'      => 'nullable|boolean',
            'offline_payment_details'     => 'nullable|string',
            'stripe_pub_key'              => 'nullable|string',
            'stripe_secret_key'           => 'nullable|string',
            'paypal_mode'                 => 'required|in:live,sandbox',
            'paypal_client_id'            => 'nullable|string',
            'paypal_app_secret'           => 'nullable|string',
            'pesapal_consumer_key'        => 'nullable|string',
            'pesapal_consumer_secret'     => 'nullable|string',
            'pesapal_live'                => 'nullable|boolean',
            'paystack_public_key'         => 'nullable|string',
            'paystack_secret_key'         => 'nullable|string',
            'flutterwave_public_key'      => 'nullable|string',
            'flutterwave_secret_key'      => 'nullable|string',
            'flutterwave_encryption_key'  => 'nullable|string',
        ]);

        // Normalize boolean fields explicitly
        $validated['enable_offline_payment'] = $request->has('enable_offline_payment') ? (bool) $request->enable_offline_payment : false;
        $validated['pesapal_live'] = $request->has('pesapal_live') ? (bool) $request->pesapal_live : false;

        try {
            $settings = Settings::first();

            if (!$settings) {
                return redirect()->back()->with('error', 'Settings record not found.');
            }

            $settings->update($validated);

            Log::info('Payment Settings Updated:', $validated);

            return redirect()->back()->with('success', 'Payment settings updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating payment settings: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while updating payment settings.');
        }
    }


    public function updateSubscription(Request $request)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'payment_mode' => 'required|in:offline,flutterwave',
            'transaction_id' => 'required_if:payment_mode,offline',
        ]);

        // Fetch the subscription
        $subscription = Subscription::findOrFail($validated['subscription_id']);

        // Handle offline payment
        if ($validated['payment_mode'] === 'offline') {
            $subscription->paid_via = 'offline';
            $subscription->payment_transaction_id = $validated['transaction_id'];
            $subscription->save();

            return back()->with('success', 'Offline payment details Recieved. Please wait for approval.');
        }


        return back()->with('error', 'Invalid payment mode selected.');
    }

    
    public function postFlutterwavePaymentCallback(Request $request)
    {
        $settings = Settings::first();
        $url = 'https://api.flutterwave.com/v3/transactions/'.$request->get('transaction_id').'/verify';
        $header = [
            'Content-Type: application/json',
            'Authorization: Bearer '.$settings->flutterwave_secret_key,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => $header,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        $payment = json_decode($response, true);


        if ($payment['status'] == 'success') {
            // Payment subscription
            $business_id = $payment['data']['meta']['business_id'];
            $package_id = $payment['data']['meta']['package_id'];
            $subscription_id = $payment['data']['meta']['subscription_id'];
            $gateway = $payment['data']['meta']['gateway'];
            $payment_transaction_id = $payment['data']['tx_ref'];
            $user_id = $payment['data']['meta']['user_id'];
            $price = $payment['data']['amount'];
        
            $business = Business::findOrFail($business_id);
            $owner = $business->owner;
            // Fetch package details for duration calculation
            $package = Package::findOrFail($package_id);
        
            $subscription = Subscription::findOrFail($subscription_id);
            $start_date = now();
            $end_date = (clone $start_date)->add($package->interval_count, $package->interval);
            
            $subscription->paid_via = $gateway;
            $subscription->status = 'approved';
            $subscription->payment_transaction_id = $payment_transaction_id;
            $subscription->trial_end_date =  $start_date;
            $subscription->start_date = $start_date;
            $subscription->end_date = $end_date;
            $subscription->save();
    
            // Return a success responsecheck.subcription
            return redirect()->route('check.subcription')->with('success', 'Payment successfully.');
        } else {
            return redirect()->route('check.subcription')->with('error', 'Something went wrong. Please try again.');
        }

    }

    
    public function index(Request $request)
    {
         // Get the selected date filter, default to 'today'
        $dateFilter = $request->input('date-filter', 'today');
        $startDate = null;
        $endDate = Carbon::now();
    
        // Set start date based on the selected date filter
        switch ($dateFilter) {
            case 'this-week':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'this-month':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'this-year':
                $startDate = Carbon::now()->startOfYear();
                break;
            case 'today':
            default:
                $startDate = Carbon::now()->startOfDay();
                break;
        }
        
        // Fetch New Subscriptions based on the date range
        $newSubscriptions = Subscription::whereBetween('created_at', [$startDate, $endDate])->count();
    
        // Fetch New Business Registrations based on the date range
        $newBusinessRegistrations = Business::whereBetween('created_at', [$startDate, $endDate])->count();
    
        // Fetch Not Subscribed users (Subscription status = 'waiting')
        $notSubscribed = Subscription::where('status', 'waiting')
                                    ->whereBetween('created_at', [$startDate, $endDate])
                                    ->count();
    
        // Pass data to the view
        return view('superadmin_saas.index', [
            'newSubscriptions' => $newSubscriptions,
            'newBusinessRegistrations' => $newBusinessRegistrations,
            'notSubscribed' => $notSubscribed,
            'dateFilter' => $dateFilter,
        ]);
    }
    
    public function getDashboardData(Request $request)
    {
        // Get the start and end date from the request and parse to Carbon instances
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
    
        // Get the number of new subscriptions within the date range
        $newSubscriptions = Subscription::whereBetween('created_at', [$startDate, $endDate])->count();
    
        // Get the number of new business registrations within the date range
        $newRegistrations = Business::whereBetween('created_at', [$startDate, $endDate])->count();
    
        // Get the number of inactive subscriptions (Not Subscribed)
        $notSubscribed = Subscription::where('status', 'waiting')
                                      ->whereBetween('created_at', [$startDate, $endDate])
                                      ->count();
    
        // Return the data as a JSON response
        return response()->json([
            'new_subscriptions' => $newSubscriptions,
            'new_registrations' => $newRegistrations,
            'not_subscribed' => $notSubscribed,
        ]);
    }
    
    public function getMonthlySubscriptionData()
    {
        // Query to get subscription data grouped by month and year
        $monthlyData = Subscription::select(
            DB::raw('MONTH(start_date) as month'),
            DB::raw('YEAR(start_date) as year'),
            DB::raw('SUM(package_price) as total_amount')
        )
        ->whereYear('start_date', now()->year) // Filter by current year
        ->groupBy(DB::raw('MONTH(start_date)'), DB::raw('YEAR(start_date)'))
        ->orderBy(DB::raw('MONTH(start_date)'))
        ->get();

        // Prepare the data for the frontend (months and amounts)
        $months = [];
        $amounts = [];

        // Fill months array and corresponding amounts
        for ($i = 1; $i <= 12; $i++) {
            $month = $monthlyData->firstWhere('month', $i);
            $months[] = \Carbon\Carbon::create()->month($i)->format('M'); // Format month as short name (Jan, Feb, etc.)
            $amounts[] = $month ? $month->total_amount : 0; // If no data for this month, set it to 0
        }

        // Return the data as JSON
        return response()->json([
            'months' => $months,
            'amounts' => $amounts,
        ]);
    }
    
    // Package Section
    // Function to view all packages in superadmin.packages
    public function packages()
    {
        // Paginate the packages, with 10 per page (you can adjust this as needed)
        $packages = Package::latest()->paginate(10);
        // Pass packages data to the view
        return view('superadmin_saas.packages', compact('packages'));
    }
    // To add a package at URL superadmin/packages/add
    public function addPackages()
    {
        $package = null;
        return view('superadmin_saas.form_package', compact('package')); // You need to create this view for the add form
    }
    // Function to store the new package in the database
    public function storePackage(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            // 'duration' => 'required|integer|min:1',
            // 'features' => 'required|string',
            'is_one_time' => 'required|boolean',
            'is_active' => 'required|boolean',
            'trial_days' => 'nullable|integer|min:0',
            'interval' => 'required|in:days,months,years',
            'interval_count' => 'required|integer|min:1',
            'user_count' => 'required|integer|min:1',
            'mark_package_as_popular' => 'required|boolean',
        ]);
        // Create the new package
        Package::create($validatedData);

        // Redirect to the package list with a success message
        return redirect()->route('superadmin.packages')->with('success', 'Package added successfully!');
    }
    // To edit a package at URL superadmin/packages/edit/{id}
    public function editPackages($id)
    {
        $package = Package::findOrFail($id); // Retrieve the package by ID
        return view('superadmin_saas.form_package', compact('package')); // Pass the package to the view
    }
    // Function to update package
    public function updatePackages(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        $package->update($request->all());
        return redirect()->route('superadmin.packages')->with('success', 'Package updated successfully');
    }
    // To delete a package at URL superadmin/packages/delete/{id}
    public function deletePackages($id)
    {
        $package = Package::findOrFail($id); // Retrieve the package by ID
        $package->delete(); // Delete the package
        return redirect()->route('superadmin.packages')->with('success', 'Package deleted successfully');
    }
    
    // Function to view all businesses in superadmin.businesses
    public function businesses()
    {
        // Fetch all packages from the database
        $packages = Package::where('is_active', true)->get();
        $paymentMethods = ['offline'];
        $businesses = Business::orderBy('created_at', 'desc')->paginate(10); // Adjust pagination as needed
        return view('superadmin_saas.businesses', compact('businesses', 'packages', 'paymentMethods'));
    }
    // To add a business at URL superadmin/businesses/add
    public function addBusiness()
    {
        // Fetch all packages from the database
        $packages = Package::where('is_active', true)->get();
        // Payment methods can be hard-coded or fetched from the database if you have a separate model for it
        // $paymentMethods = ['Bank Transfer', 'Credit Card', 'PayPal'];  // Example payment methods
        $paymentMethods = ['offline'];
        $business = null;  // You can initialize the business object if needed
        
        return view('superadmin_saas.form_business', compact('business', 'packages', 'paymentMethods'));
    }
    
    public function storeBusiness(Request $request)
    {
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email',
            'contact' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'website' => 'nullable|url',
            'start_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users,user_name',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed', // password confirmation validation
        ]);
        
        
        // Validate Business data
        $validatedBusinessData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email',
            'contact' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'website' => 'nullable|url',
            'start_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        // Validate User (owner) data
        $validatedUserData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'user_name' => 'required|string|max:255|unique:users,user_name',
                'email' => 'required|email|unique:users,email',
                'phone_number' => 'required|string|max:20|unique:users,phone',
                'password' => 'required|string|min:8|confirmed', // password confirmation validation
        ]);
    
        // Add the currently authenticated user's ID as 'created_by'
        $validatedBusinessData['created_by'] =   auth()->user()->id;
        $validatedBusinessData['start_date'] = now();
    
        DB::beginTransaction();
    
        try {
            // First create the Business (so we can get its ID)
            $business = Business::create($validatedBusinessData);
    

    
            $owner = User::create([
                'user_name' => $validatedUserData['user_name'],
                'first_name' => $validatedUserData['first_name'],
                'last_name' => $validatedUserData['last_name'],
                'email' => $validatedUserData['email'],
                'phone' => $validatedUserData['phone_number'],
                'phone_verified_at' => now(),
                'password' => bcrypt($validatedUserData['password']),
                'code' => 'GPS' . rand(100000, 999999),
                'email_verified_at' => now(),
            ]);
            
            $owner->assignRole('admin');
            // Add owner_id and package_id to the Business data
            $business->owner_id = $owner->id;
            $business->package_id = $request->input('package');
            $business->save();
    
            // Handle file upload for the logo if provided
            if ($request->hasFile('logo')) {
                $business->logo = $request->file('logo')->store('logos', 'public');
                $business->save();
            }
    
            // Update the business_id in the user table
            $owner->business_id = $business->id;
            $owner->save();
    
            // Fetch the selected Package from the request
            $package = Package::findOrFail($request->input('package'));
    
            // Create a Subscription for the newly created business
            $subscriptionData = [
                'user_id' => $owner->id, // link the subscription to the owner
                'package_id' => $package->id, // Package ID from the selected Package
                'start_date' => now(), // Assuming the subscription starts now
                'end_date' => now()->add($package->interval_count, $package->interval), // Dynamically add subscription duration
                'business_id' => $business->id, // Link the subscription to the created business
                'payment_transaction_id' => $request->input('transaction_id'), // Transaction ID from the form
                'paid_via' => $request->input('paid_via'), // Payment method from the form
                'trial_end_date' => $package->trial_days ? now()->addDays($package->trial_days) : null, // Trial end date from the package's trial_days
                'original_price' => $package->price, // Price from the selected Package
                'package_price' => $package->price, // Package price from the selected Package
                'package_details' => $package->details, // Package details from the selected Package
            ];
    
            // Create the Subscription
            Subscription::create($subscriptionData);
    
            DB::commit();
    
            // Redirect with success message
            return redirect()->route('superadmin.businesses')->with('success', 'Business and owner created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create business and owner: ' . $e->getMessage()]);
        }
    }

    // To edit a business at URL superadmin/businesses/edit/{id}
    public function editBusiness($id)
    {
        // Fetch all packages from the database
        $packages = Package::where('is_active', true)->get();
        // Payment methods can be hard-coded or fetched from the database if you have a separate model for it
        // $paymentMethods = ['Bank Transfer', 'Credit Card', 'PayPal'];  // Example payment methods
        $paymentMethods = ['offline'];
        $business = Business::findOrFail($id); // Retrieve the business by ID
        return view('superadmin_saas.form_business', compact('business', 'packages', 'paymentMethods')); // Pass the business to the view
    }


    public function updateBusiness(Request $request, $id)
    {
        // Find the Business by ID
        $business = Business::findOrFail($id);
        $owner = $business->owner;
    
        // Validate Business data
        $validatedBusinessData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:businesses,email,' . $business->id,
            'contact' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'website' => 'nullable|url',
            'start_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        
    
        // Validate User (owner) data
        $validatedUserData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users,user_name,' . $owner->id,
            'email' => 'required|email|unique:users,email,' . $owner->id,
            'phone_number' => 'required|string|max:20|unique:users,phone_number,' . $owner->id,
            'password' => 'nullable|string|min:8|confirmed', // Password update is optional
        ]);
    
        DB::beginTransaction();
    
        try {
            // Update the user (owner) details with Sentinel
            $owner->first_name = $validatedUserData['first_name'];
            $owner->last_name = $validatedUserData['last_name'];
            $owner->user_name = $validatedUserData['user_name'];
            $owner->email = $validatedUserData['email'];
            $owner->phone_number = $validatedUserData['phone_number'];
    
            // Update password if provided
            if (!empty($validatedUserData['password'])) {
                $owner->password = bcrypt($validatedUserData['password']);
            }
            $owner->save();
    
            // Ensure the owner still has the Admin role
            $adminRole = Sentinel::findRoleBySlug('admin');
            if ($adminRole && !$owner->inRole('admin')) {
                $adminRole->users()->attach($owner);
            }
    
            // Handle file upload for the logo if provided
            if ($request->hasFile('logo')) {
                $validatedBusinessData['logo'] = $request->file('logo')->store('logos', 'public');
            }
    
            // Update the Business details
            $business->update($validatedBusinessData);
    
            DB::commit();
    
            // Redirect with a success message
            return redirect()->route('superadmin.businesses')->with('success', 'Business and owner updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update business and owner: ' . $e->getMessage()]);
        }
    }


    public function toggleBusinessStatus(Request $request, $id)
    {
        // $business = Business::findOrFail($id);
        // $business->is_active = $request->status === 'active';
        // $business->save();
    
        // return response()->json(['success' => true]);
        
        
        $business = Business::findOrFail($id);
        $business->is_active = $request->input('is_active');
        $success = $business->save();
    
        return response()->json(['success' => $success]);
    }
    
    
    public function addBusinessSubscriptionV2(Request $request, $businessId)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'package_id' => 'required|exists:packages,id',
                'payment_method' => 'required|string',
                'transaction_id' => 'required|string|unique:subscriptions,payment_transaction_id',
            ]);
    
            // Fetch the selected Package and Business
            $business = Business::findOrFail($businessId);
            $owner = $business->owner; 
            // Check if the user already has an active subscription
            $existingSubscription = Subscription::where('user_id', $owner->id)
                    ->where('status', 'approved')
                    ->first();
            \Log::error('Error active subscription: ' . $existingSubscription);
            
            if ($existingSubscription) {
                // Return a message indicating the user already has an active subscription
                return redirect()->route('superadmin.businesses')->with('error', 'User already has an active subscription.');
            }
            $package = Package::findOrFail($request->input('package_id'));  // Ensure correct input field name
            
            // Prepare subscription data
            $subscriptionData = [
                'user_id' => $owner->id, // link the subscription to the owner
                'package_id' => $package->id, // Package ID from the selected Package
                'start_date' => now(), // Subscription starts now
                'end_date' => now()->add($package->interval_count, $package->interval), // Dynamic subscription duration
                'business_id' => $business->id, // Link the subscription to the created business
                'payment_transaction_id' => $request->transaction_id,
                'paid_via' => $request->payment_method, // Correctly referencing the payment method
                'trial_end_date' => $package->trial_days ? now()->addDays($package->trial_days) : null, // Trial end date if exists
                'original_price' => $package->price, // Price from the selected Package
                'package_price' => $package->price, // Package price from the selected Package
                'package_details' => $package->details, // Package details from the selected Package
            ];
    
            // Create the subscription
            Subscription::create($subscriptionData);
    
            // Log success
            Log::info('Subscription added successfully for business ID: ' . $businessId, $subscriptionData);
    
            // Return a success response
            return redirect()->route('superadmin.businesses')->with('success', 'Subscription added successfully.');
        
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error adding subscription for business ID: ' . $businessId, [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
    
            // Return an error response
            return redirect()->route('superadmin.businesses')->with('error', 'An error occurred while adding the subscription.');
        }
    }
    
    public function addBusinessSubscription(Request $request, $businessId)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'package_id' => 'required|exists:packages,id',
                'payment_method' => 'required|string',
                'transaction_id' => 'required|string|unique:subscriptions,payment_transaction_id',
            ]);
    
            // Fetch the selected Package and Business
            $business = Business::findOrFail($businessId);
            $owner = $business->owner;
    
            // Get the latest active subscription based on end_date
            $latestSubscription = Subscription::where('business_id', $businessId)
                ->where('status', 'approved') // Only consider approved subscriptions
                ->orderBy('end_date', 'desc')
                ->first();
    
            // Ensure $start_date is a Carbon instance
            $start_date = $latestSubscription && $latestSubscription->end_date 
                ? Carbon::parse($latestSubscription->end_date) 
                : now();
    
            // Fetch package details for duration calculation
            $package = Package::findOrFail($request->input('package_id'));
            
            // Calculate the end_date based on package interval
            $end_date = (clone $start_date)->add($package->interval_count, $package->interval);
            
            // Prepare subscription data
            $subscriptionData = [
                'user_id' => $owner->id,
                'package_id' => $package->id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => 'approved',
                'business_id' => $business->id,
                'payment_transaction_id' => $request->transaction_id,
                'paid_via' => $request->payment_method,
                'trial_end_date' => $package->trial_days ? (clone $start_date)->addDays($package->trial_days) : null,
                'original_price' => $package->price,
                'package_price' => $package->price,
                'package_details' => $package->details,
            ];
    
            // Create the new subscription
            Subscription::create($subscriptionData);
    
            // Log success
            // Log::info('Subscription added successfully for business ID: ' . $businessId, $subscriptionData);
    
            // Return a success response
            return redirect()->route('superadmin.businesses')->with('success', 'Subscription added successfully.');
        
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error adding subscription for business ID: ' . $businessId, [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
    
            // Return an error response
            return redirect()->route('superadmin.businesses')->with('error', 'An error occurred while adding the subscription.');
        }
    }


    
    public function addSubscription(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'package' => 'required|exists:packages,id',
                'payment_method' => 'required|string',
                'businessId' => 'required|int',
                'transaction_id' => 'nullable|string',
            ]);
    
            // Find the business
            $business = Business::findOrFail($request->businessId);
            
            // Check if the user already has an active subscription
            $existingSubscription = Subscription::where('user_id', $business->owner->id)
                ->where('status', 'approved')
                ->first();
            \Log::error('Error active subscription: ' . $existingSubscription);
            if ($existingSubscription) {
                // Return a message indicating the user already has an active subscription
                return response()->json(['success' => false, 'message' => 'User already has an active subscription.'], 400);
            }
    
            // Find the selected package
            $package = Package::findOrFail($request->package);
    
            // Create a new subscription
            $subscription = new Subscription([
                'user_id' => $business->owner->id, // Link the subscription to the owner
                'package_id' => $package->id, // Package ID from the selected Package
                'start_date' => now(), // Subscription starts now
                'end_date' => now()->add($package->interval_count, $package->interval), // End date dynamically calculated
                'business_id' => $business->id, // Link the subscription to the business
                'payment_transaction_id' => $request->transaction_id,
                'paid_via' => $request->payment_method,
                'trial_end_date' => $package->trial_days ? now()->addDays($package->trial_days) : null, // Trial end date
                'original_price' => $package->price, // Original price from the selected Package
                'package_price' => $package->price, // Package price
                'package_details' => $package->details, // Package details
            ]);
    
            // Save the new subscription
            $subscription->save();
    
            // Return success response
            return response()->json(['success' => true, 'message' => 'Subscription added successfully.']);
    
        } catch (\Exception $e) {
            // Log the error (optional)
            \Log::error('Error adding subscription: ' . $e->getMessage());
    
            // Return a response indicating an error occurred
            return response()->json(['success' => false, 'message' => 'An error occurred while adding the subscription. Please try again later.'], 500);
        }
    }

    // To delete a business at URL superadmin/businesses/delete/{id}
    public function deleteBusiness($id)
    {
        $business = Business::findOrFail($id); // Retrieve the business by ID
        $business->delete(); // Delete the business
        return redirect()->route('superadmin.businesses')->with('success', 'Business deleted successfully');
    }
    
    public function showBusiness($id)
    {
        // Fetch the business details with related data
        $business = Business::with(['subscriptions', 'users'])->findOrFail($id);
    
        // Fetch the current or latest active subscription for the business
        $currentSubscription = Subscription::where('business_id', $id)
                            ->where(function($query) {
                                $query->where('status', 'approved')
                                      ->orWhere('end_date', '>=', now());
                            })
                            ->latest('end_date')
                            ->first();
    
        // Pass data to the view
        return view('superadmin_saas.show_business', compact('business', 'currentSubscription'));
    }
    
    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();
    
        return response()->json(['success' => true]);
    }
    
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        $user = User::findOrFail($id);
        $user->password = bcrypt($request->password);
        $user->save();
    
        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    
    public function showSubscriptions()
    {
        $subscriptions = Subscription::with(['business', 'user'])
                            ->orderBy('created_at', 'desc') // Ensure ordering explicitly
                            ->paginate(10); // Paginate the results
        return view('superadmin_saas.subscription', compact('subscriptions'));
    }

   // Update subscription status
    public function updateStatus(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);
        // Update status
        $subscription->status = $request->status;
    
        // If status is 'approved', set the start_date to the current time
        if ($request->status == 'approved') {
            $subscription->start_date = Carbon::now();
            
            // Calculate the end_date based on the package's interval count and interval type (day, month, year, etc.)
            $subscription->end_date = Carbon::now()->add($subscription->package->interval_count, $subscription->package->interval);
        }
        
        $subscription->status = $request->status;
        $subscription->start_date = Carbon::now();
        $subscription->payment_transaction_id = $request->payment_transaction_id;
        $subscription->save();
    
        return redirect()->route('superadmin.subscriptions')->with('success', 'Subscription status updated successfully.');
    }
    
    // Update subscription dates
    public function updateDates(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->start_date = $request->start_date;
        $subscription->end_date = $request->end_date;
        $subscription->trial_end_date = $request->trial_end_date;
        $subscription->save();
    
        return redirect()->route('superadmin.subscriptions')->with('success', 'Subscription dates updated successfully.');
    }
    
    public function superAdminSettings()
    {
        $settings = Settings::first();

        return view('superadmin_saas.settings', compact('settings'));
    }
    
    public function superAdminCommunicator()
    {
        $businesses = Business::all(['id', 'name']); // Fetch all businesses with 'id' and 'name'
        // $messages = Message::latest()->paginate(10);
        // Group messages by subject and paginate them
        $messages = Message::select('subject', DB::raw('MAX(content) as message'), DB::raw('MAX(created_at) as created_at'))
        ->groupBy('subject') // Group by subject
        ->latest('created_at') // Order by latest created_at
        ->paginate(10); // Paginate results
        Log::error('Messages: ' . $messages);
        return view('superadmin_saas.communicator', compact('businesses','messages'));
    }

    public function updateBasicSettings(Request $request)
    {
        if (env('DEMO_MODE')) {
            return redirect()->route('superadmin.settings')->with('error', 'This action is disabled in demo mode');
        }

        $appName = $request->appName;
        $appVersion = $request->appVersion;
        $country = $request->country;
        $phoneCountryCode = $request->phoneCountryCode;
        $currency = $request->currency;
        $currencySymbol = $request->currencySymbol;
        $distanceUnit = $request->distanceUnit;

        if ($appName == null) {
            return redirect()->route('superadmin.settings')->with('error', 'App Name is required');
        }

        if ($appVersion == null) {
            return redirect()->route('superadmin.settings')->with('error', 'App Version is required');
        }

        if ($country == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Country is required');
        }

        if ($phoneCountryCode == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Phone Country Code is required');
        }

        if ($currency == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Currency is required');
        }

        if ($currencySymbol == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Currency Symbol is required');
        }

        if ($distanceUnit == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Distance Unit is required');
        }


        $settings = Settings::first();
        $settings->app_name = $appName;
        $settings->app_version = $appVersion;
        $settings->country = $country;
        $settings->phone_country_code = $phoneCountryCode;
        $settings->currency = $currency;
        $settings->currency_symbol = $currencySymbol;
        $settings->distance_unit = $distanceUnit == 'KM' ? 'km' : 'miles';
        $settings->save();

        return redirect()->route('superadmin.settings')->with('success', 'Settings Updated Successfully');
    }

    public function updateDashboardSettings(Request $request)
    {
        if (env('DEMO_MODE')) {
            return redirect()->route('superadmin.settings')->with('error', 'This action is disabled in demo mode');
        }

        $offlineCheckTimeType = $request->offlineCheckTimeType;
        $offlineCheckTime = $request->offlineCheckTime;

        if ($offlineCheckTimeType == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Offline Check Time Type is required');
        }

        if ($offlineCheckTime == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Offline Check Time is required');
        }

        $settings = Settings::first();
        $settings->offline_check_time_type = $offlineCheckTimeType;
        $settings->offline_check_time = $offlineCheckTime;
        $settings->save();

        return redirect()->route('superadmin.settings')->with('success', 'Settings Updated Successfully');
    }

    public function updateMobileAppSettings(Request $request)
    {
        if (env('DEMO_MODE')) {
            return redirect()->route('superadmin.settings')->with('error', 'This action is disabled in demo mode');
        }
        $mobileAppVersion = $request->mobileAppVersion;
        $privacyPolicyLink = $request->privacyPolicyLink;
        $locationUpdateIntervalType = $request->locationUpdateIntervalType;
        $locationUpdateInterval = $request->locationUpdateInterval;

        if ($mobileAppVersion == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Mobile App Version is required');
        }

        if ($privacyPolicyLink == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Privacy Policy Link is required');
        }

        if ($locationUpdateIntervalType == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Location Update Interval Type is required');
        }

        if ($locationUpdateInterval == null) {
            return redirect()->route('superadmin.settings')->with('error', 'Location Update Interval is required');
        }

        $settings = Settings::first();
        $settings->m_app_version = $mobileAppVersion;
        $settings->privacy_policy_url = $privacyPolicyLink;
        $settings->m_location_update_time_type = $locationUpdateIntervalType;
        $settings->m_location_update_interval = $locationUpdateInterval;
        $settings->save();

        return redirect()->route('superadmin.settings')->with('success', 'Settings Updated Successfully');
    }

   public function sendMessage(Request $request)
   {
        // Validate the incoming data
        $validatedData = $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'exists:businesses,id', // Ensure recipients exist
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:65535',
        ]);
    
        // Prepare the data for batch insert
        $messages = [];
       
        foreach ($validatedData['recipients'] as $businessId) {
            $messages[] = [
                'business_id' => $businessId,
                'subject' => $validatedData['subject'],
                'content' => $validatedData['message'],
                'is_read' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        // Log the messages array
        // \Log::error('Messages to be inserted: ' . json_encode($messages));
        // Perform the batch insert
        Message::insert($messages);
    
        // Return a success response
        return redirect()->route('superadmin.communicator')
                         ->with('success', 'Message sent successfully.');                   
    }



}
