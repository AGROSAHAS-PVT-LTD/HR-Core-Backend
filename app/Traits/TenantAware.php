<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait TenantAware
{
    /**
     * Boot the TenantAware trait to automatically add the business_id scope to queries.
     */
    protected static function bootTenantAware()
    {
        // Skip Tenant logic on API requests
        if (request()->is('api/*')) {
            return;
        }

        // Assign `business_id` during model creation
        static::creating(function ($model) {
            if (Auth::check() && !isset($model->business_id)) {
                $user = Auth::user();
                // Set business_id always (even if superuser)
                $model->business_id = $user->business_id;
            }
        });

        // Add global scope to filter by `business_id` if not superuser
        static::addGlobalScope('business_id', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

                if (!$user->is_superuser) {
                    $builder->where('business_id', $user->business_id);
                } else {
                    // Optional: for superusers, apply only to non-user tables
                    if ($builder->getModel()->getTable() !== 'users') {
                        $builder->where('business_id', $user->business_id ?? 0);
                    }
                }
            }
        });
    }
}
