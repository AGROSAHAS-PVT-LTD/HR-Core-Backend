<?php

namespace App\Traits;

use App\Enums\LeaveRequestStatus;
use App\Models\Attendance;
use App\Models\BankAccount;
use App\Models\Designation;
use App\Models\DigitalIdCard;
use App\Models\ExpenseRequest;
use App\Models\LeaveRequest;
use App\Models\LoanRequest;
use App\Models\PaymentCollection;
use App\Models\PayrollAdjustment;
use App\Models\SalesTarget;
use App\Models\Shift;
use App\Models\Site;
use App\Models\Team;
use App\Models\User;
use App\Models\UserAvailableLeave;
use App\Models\UserDevice;
use App\Models\UserSettings;

trait UserTenantOptionsTrait
{

  public function userDevice()
  {
    return $this->hasOne(UserDevice::class);
  }

  public function team()
  {
    return $this->belongsTo(Team::class);
  }


  public function shift()
  {
    return $this->belongsTo(Shift::class);
  }

  public function userAvailableLeaves()
  {
    return $this->hasMany(UserAvailableLeave::class);
  }


  public function reportingTo()
  {
    return $this->belongsTo(User::class, 'reporting_to_id');
  }


  public function isOnLeave(): bool
  {
    return LeaveRequest::where('user_id', $this->id)
      ->where('status', LeaveRequestStatus::APPROVED)
      ->where('from_date', '<=', now()->toDateString())
      ->where('to_date', '>=', now()->toDateString())
      ->exists();
  }

  public function designation()
  {
    return $this->belongsTo(Designation::class);
  }

  public function getReportingToUserName()
  {
    $user = User::find($this->reporting_to_id);
    return $user ? $user->getFullName() : '';
  }


  public function userSettings()
  {
    return $this->hasOne(UserSettings::class);
  }

  public function digitalIdCard()
  {
    return $this->hasOne(DigitalIdCard::class);
  }

  public function leaveRequests()
  {
    return $this->hasMany(LeaveRequest::class);
  }

  public function expenseRequests()
  {
    return $this->hasMany(ExpenseRequest::class);
  }

  public function loanRequests()
  {
    return $this->hasMany(LoanRequest::class);
  }

  public function paymentCollections()
  {
    return $this->hasMany(PaymentCollection::class);
  }

  public function site()
  {
    return $this->belongsTo(Site::class);
  }

  public function salesTargets()
  {
    return $this->hasMany(SalesTarget::class);
  }

  public function getTodayAttendance()
  {
    return $this->attendances()
      ->whereDate('check_in_time', now()->toDateString())
      ->first();
  }

  public function attendances()
  {
    return $this->hasMany(Attendance::class);
  }


  public function bankAccount()
  {
    return $this->hasOne(BankAccount::class);
  }


  public function payrollAdjustments()
  {
    return $this->hasMany(PayrollAdjustment::class)->where('user_id', $this->id);
  }


}
