<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // Fillable fields
    protected $fillable = [
        'business_id',
        'subject',
        'content',
        'is_read',
    ];

    // Relationship with the Business model
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
