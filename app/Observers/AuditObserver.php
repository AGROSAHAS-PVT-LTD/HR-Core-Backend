<?php

namespace App\Observers;

use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditObserver
{
    public function creating(Audit $audit)
    {
        if (Auth::check()) {
            $audit->business_id = Auth::user()->business_id;
            Log::info('AuditObserver: business_id set to ' . $audit->business_id);
        } else {
            Log::warning('AuditObserver: no authenticated user found');
        }
    }
}
