<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

trait TenantAwareUser
{
    protected static function bootTenantAwareUser()
    {
        if (app()->runningInConsole() || (request() && request()->is('api/*'))) {
            Log::info('TenantAwareUser trait skipped on API request or console.');
            return;
        }
    
        // This will be set once at boot, often null or first available user
        $user = request()->user() ?? auth()->user();
    
        Log::info('TenantAwareUser boot, user at boot time:', ['user' => $user ? $user->id : 'null']);
    
        static::creating(function ($model) use ($user) {
            if ($user) {
                Log::info('TenantAwareUser creating event - user found', ['user_id' => $user->id]);
    
                if (!isset($model->business_id)) {
                    $model->business_id = $user->business_id;
                    Log::info('Assigned business_id to model', ['business_id' => $model->business_id]);
                }
    
                if (!isset($model->business_id)) {
                    Log::error('business_id is missing, aborting model creation.');
                    return false; // abort create
                }
            } else {
                Log::warning('No authenticated user found at boot, aborting model creation.');
                return false; // abort create
            }
        });
    
        static::addGlobalScope('business_id', function (Builder $builder) use ($user) {
            Log::info('TenantAwareUser User at query time:', ['user' => $user ? $user->id : 'null']);
    
            if ($user) {
                Log::info('TenantAwareUser global scope applied', ['user_id' => $user->id, 'is_superuser' => $user->is_superuser]);
    
                if (!$user->is_superuser) {
                    $builder->where('business_id', $user->business_id);
                    Log::info('Applied business_id filter for non-superuser', ['business_id' => $user->business_id]);
                } else {
                    if ($builder->getModel()->getTable() !== 'users') {
                        $builder->where('business_id', $user->business_id ?? 0);
                        Log::info('Applied business_id filter for superuser on non-users table', ['business_id' => $user->business_id ?? 0]);
                    }
                }
            } else {
                Log::warning('No authenticated user found for global scope at boot, no filtering applied.');
            }
        });
    }

}
