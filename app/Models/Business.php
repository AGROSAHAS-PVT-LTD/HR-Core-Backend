<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'contact', 'logo', 'country', 'city',
        'website', 
        'start_date', 'address', 'created_by',
        'description', 'owner_id' // Make sure to add 'owner_id' to the fillable properties
    ];

    // Relationship to access the owner of the business
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id'); // 'owner_id' is the foreign key in the businesses table
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship to access the users related to the business
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relationship to access subscriptions related to the business
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    
    // Relationship with messages
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

}
