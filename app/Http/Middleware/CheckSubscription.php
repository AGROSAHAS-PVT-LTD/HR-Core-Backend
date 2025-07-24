<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Check if the user is a super-admin
        if ($user->is_superuser) {
            Log::info('Super-admin detected', ['user_id' => $user->id]);
            return $next($request);
        }
        Log::info('User detected', ['user_id' => $user->id]);

        // Check if the user has a business
        $business = Business::find($user->business_id);
        
        if (!$business) {
            Log::info('Super-admin detected', ['user_id' => $user->id]);
            return abort(403, 'No business associated with this user.');
        }
        
        // Check if the business is active
        if (!$business->is_active) {
           return redirect()->to('/check_subcription');
        }

        // Check for an active subscription for the user's business
        $activeSubscription = Subscription::where('business_id', $business->id)
            ->orderBy('end_date', 'desc')
            ->first();

        if (!$activeSubscription) {
            return redirect()->to('/check_subcription');
        }

        // Check if the subscription is in trial period or active
        if (!$activeSubscription->isActive() && !$activeSubscription->isInTrialPeriod()) {
           return redirect()->to('/check_subcription');
        }

        return $next($request);
    }
}
