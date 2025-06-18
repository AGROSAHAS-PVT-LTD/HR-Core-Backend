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
        // 'features',
        'is_one_time',
        'is_active',
        'trial_days',
        'interval',
        'interval_count',
        'user_count',
        'mark_package_as_popular',
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

}
