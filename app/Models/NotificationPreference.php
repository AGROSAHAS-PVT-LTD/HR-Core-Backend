<?php

namespace App\Models;

use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
  use UserActionsTrait, TenantTrait;

  protected $fillable = ['user_id', 'preferences','business_id'
  ];
  public function business()
  {
    return $this->belongsTo(Business::class, 'business_id');
  }

  protected $casts = [
    'preferences' => 'array',
  ];
}
