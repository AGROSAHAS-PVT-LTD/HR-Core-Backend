<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    
    use HasFactory;

    protected $table = 'packages';

    protected $fillable = [
        'name',
        'description',
        'price',
        'features',
        'is_one_time',
        'is_active',
        'trial_days',
        'interval',
        'interval_count',
        'user_count',
        'mark_package_as_popular','yearly_price',
        'has_yearly_plan',
    ];

    // Define the relationship with subscriptions
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
    
    /**
     * Get price based on plan type.
     *
     * @param string $planType ('monthly' or 'yearly')
     * @return float|null
     */ 
    public function getPrice($planType)
    {
        if ($planType == 'yearly') {
            return $this->yearly_price;
        }
    
        return $this->price; // Default to monthly price
    }

}
