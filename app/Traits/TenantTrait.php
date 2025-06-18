<?php

namespace App\Traits;

use App\Models\Tenant;

trait TenantTrait
{
  public function tenant()
  {
    return $this->belongsTo(Tenant::class);
  }
}
