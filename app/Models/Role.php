<?php

namespace App\Models;
use App\Traits\TenantTrait;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{

  use TenantTrait;

  protected $fillable = [
    'name',
    'guard_name',
    'is_location_activity_tracking_enabled',
    'is_mobile_app_access_enabled',
    'is_multiple_check_in_enabled',
    'business_id'
  ];
  
  public function business()
  {
    return $this->belongsTo(Business::class, 'business_id');
  }

  protected $casts = [
    'is_location_activity_tracking_enabled' => 'boolean',
    'is_mobile_app_access_enabled' => 'boolean',
    'is_multiple_check_in_enabled' => 'boolean'
  ];
}
