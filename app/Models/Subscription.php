<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Subscription extends Model
{
    use HasFactory;
    // use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'package_id',
        'start_date',
        'end_date',
        'status',
        'business_id',               
        'payment_transaction_id',    
        'trial_end_date',             
        'original_price',             
        'package_price',              
        'package_details', 'paid_via'
    ];


    // Define the relationship with the user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    

    // Define the relationship with the business
    public function business()
    {
        return $this->belongsTo(Business::class,'business_id');
    }
    
     // Calculate remaining days until the subscription expires
    // public function getRemainingDaysAttribute()
    // {
    //     $endDate = $this->end_date ? \Carbon\Carbon::parse($this->end_date) : null;
    //     $now = \Carbon\Carbon::now();

    //     return $endDate && $endDate->greaterThan($now) ? $endDate->diffInDays($now) : 0;
    // }

    // Check if the subscription is active
    public function isActive()
    {
        return $this->status === 'approved' && $this->end_date && \Carbon\Carbon::now()->lessThanOrEqualTo($this->end_date);
    }

   
    // Format the package price
    // public function getFormattedPackagePriceAttribute()
    // {
    //     return number_format($this->package_price, 0);
    // }

    // Format the original price
    public function getFormattedOriginalPriceAttribute()
    {
        return number_format($this->original_price, 0);
    }
    
    // Accessor for formatted package price
    public function getFormattedPackagePriceAttribute()
    {
        return $this->package_price ? number_format($this->package_price, 0) : 'N/A';
    }

    // Check if the subscription is in the trial period
    public function isInTrialPeriod()
    {
        $trialEndDate = $this->trial_end_date ? \Carbon\Carbon::parse($this->trial_end_date) : null;
        return $trialEndDate && \Carbon\Carbon::now()->lessThanOrEqualTo($trialEndDate);
    }
   

    // Accessor for remaining days
    public function getRemainingDaysAttribute()
    {
        if(!$this->isInTrialPeriod() && !$this->isActive() ){
            return 0;
        }

        if ($this->isInTrialPeriod() && $this->trial_end_date) {
            $remainingDays = now()->diffInDays($this->trial_end_date, false);
            return max(0, (int) $remainingDays); // Ensures it's an integer and not negative
        }else{
            $remainingDays = now()->diffInDays($this->end_date, false);
            return max(0, (int) $remainingDays); // Ensures it's an integer and not negative
        }

        return 0; // Return 0 instead of 'N/A' for consistency
    }


     public function getSubscriptionDaysAttribute()
     {
        //  if(!$this->isInTrialPeriod() || !$this->isActive() ){
        //     return 0;
        //  }

         $start = $this->start_date ? Carbon::parse($this->start_date) : null;
     
         if (!$start) {
             return 0;
         }
     
         if ($this->isInTrialPeriod() && $this->trial_end_date) {
             $end = Carbon::parse($this->trial_end_date);
         } elseif ($this->end_date) {
             $end = Carbon::parse($this->end_date);
         } else {
             return 0;
         }
     
         $days = $start->diffInDays($end, false);
         return max(0, (int) $days);
     }



    // Check if the subscription is in trial period
    // public function isInTrialPeriod()
    // {
    //     return $this->trial_end_date && now()->lt($this->trial_end_date);
    // }
    
}
