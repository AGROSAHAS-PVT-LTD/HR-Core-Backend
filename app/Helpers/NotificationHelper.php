<?php

namespace App\Helpers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationHelper
{
  public static function notifyAdminHROld($notification, $isExceptMe = true): void
  {
    try {
      $authUser = auth()->user();

      // Retrieve HR admins, HR managers, Admin and the user's reporting manager
      $hrAdmins = User::with('roles')
                        ->where('business_id', $authUser->business_id)
                        ->whereHas('roles', function ($query) {
                          $query->where('name', 'hr')->orWhere('name', 'admin');
                  })->get();

      $managers = User::with('roles')
                        ->where('business_id', $authUser->business_id)
                        ->whereHas('roles', function ($query) {
                        $query->where('name', 'manager');
                      })->get();

      //Merge HR Admins and managers
      $hrAdmins = $hrAdmins->merge($managers);


      $reportingTo = $authUser->reportingTo;

      // Prepare list of users to notify
      $notifiables = !$reportingTo ? $hrAdmins->merge([auth()->user()])->filter() : $hrAdmins->merge([$reportingTo, auth()->user()])->filter();

      if ($isExceptMe) {
        $notifiables = $notifiables->where('id', '!=', $authUser->id);
      }

      // Send the notification
      Notification::send($notifiables, $notification);
    } catch (Exception $e) {
      Log::error($e->getMessage());
    }
  }


  public static function notifyAdminHR($notification, $isExceptMe = true): void
  {
      try {
          $authUser = auth()->user();

          // Get all relevant users in one go: HR, Admin, Manager
          $notifiables = User::where('business_id', $authUser->business_id)
              ->whereHas('roles', function ($query) {
                  $query->whereIn('name', ['hr', 'admin', 'manager']);
              })
              ->get();

          // Add reporting manager (if exists) and current user
          $extraUsers = collect([$authUser->reportingTo, $authUser])->filter(); // removes nulls

          $notifiables = $notifiables->merge($extraUsers)->unique('id');

          // Optionally exclude current user
          if ($isExceptMe) {
              $notifiables = $notifiables->where('id', '!=', $authUser->id);
          }

          // Send notification
          Notification::send($notifiables, $notification);
      } catch (\Exception $e) {
          Log::error('Notification Error: ' . $e->getMessage());
      }
  }

  public static function notifyAdmin($notification, $isExceptMe = true): void
  {
      try {
          $authUser = auth()->user();

          // Retrieve Admins in the same business
          $admins = User::where('business_id', $authUser->business_id)
              ->whereHas('roles', function ($query) {
                  $query->where('name', 'admin');
              })
              ->get();

          // Collect reporting manager and current user
          $extraUsers = collect([$authUser->reportingTo, $authUser])->filter();

          // Merge and deduplicate users
          $notifiables = $admins->merge($extraUsers)->unique('id');

          // Exclude current user if needed
          if ($isExceptMe) {
              $notifiables = $notifiables->where('id', '!=', $authUser->id);
          }

          // Send the notification
          Notification::send($notifiables, $notification);

      } catch (\Exception $e) {
          Log::error('Admin Notification Error: ' . $e->getMessage());
      }
  }



  public static function notifyAdminOld($notification, $isExceptMe = true): void
  {
    $authUser = auth()->user();

    // Retrieve Admin and the user's reporting manager
    $admins = User::with('roles')->where('business_id', $authUser->business_id)->whereHas('roles', function ($query) {
      $query->where('name', 'admin');
    })->get();

    $reportingTo = $authUser->reportingTo;

    // Prepare list of users to notify
    $notifiables = !$reportingTo ? $admins->merge([auth()->user()])->filter() : $admins->merge([$reportingTo, auth()->user()])->filter();

    if ($isExceptMe) {
      $notifiables = $notifiables->where('id', '!=', $authUser->id);
    }

    // Send the notification
    Notification::send($notifiables, $notification);
  }

  public static function notifyManagerOld($notification, $isExceptMe = true): void
  {
    $authUser = auth()->user();

    // Retrieve Admin and the user's reporting manager
    $managers = User::with('roles')->where('business_id', $authUser->business_id)->whereHas('roles', function ($query) {
      $query->where('name', 'manager');
    })->get();

    $reportingTo = $authUser->reportingTo;

    // Prepare list of users to notify
    $notifiables = !$reportingTo ? $managers->merge([auth()->user()])->filter() : $managers->merge([$reportingTo, auth()->user()])->filter();

    if ($isExceptMe) {
      $notifiables = $notifiables->where('id', '!=', $authUser->id);
    }

    // Send the notification
    Notification::send($notifiables, $notification);
  }

  public static function notifyManager($notification, $isExceptMe = true): void
  {
      try {
          $authUser = auth()->user();

          // Get all managers in the same business
          $managers = User::where('business_id', $authUser->business_id)
              ->whereHas('roles', function ($query) {
                  $query->where('name', 'manager');
              })
              ->get();

          // Prepare a list of additional users: reporting manager and current user
          $extraUsers = collect([$authUser->reportingTo, $authUser])->filter(); // removes nulls

          // Combine managers + extra users, remove duplicates
          $notifiables = $managers->merge($extraUsers)->unique('id');

          // Exclude the current user if requested
          if ($isExceptMe) {
              $notifiables = $notifiables->where('id', '!=', $authUser->id);
          }

          // Send the notification
          Notification::send($notifiables, $notification);

      } catch (\Exception $e) {
          Log::error('Manager Notification Error: ' . $e->getMessage());
      }
  }


  public static function notifyHROld($notification, $isExceptMe = true): void
  {
    $authUser = auth()->user();

    // Retrieve HR admins, HR managers, Admin and the user's reporting manager
    $hrAdmins = User::with('roles')->where('business_id', $authUser->business_id)->whereHas('roles', function ($query) {
      $query->where('name', 'hr');
    })->get();

    $reportingTo = $authUser->reportingTo;

    // Prepare list of users to notify
    $notifiables = !$reportingTo ? $hrAdmins->merge([auth()->user()])->filter() : $hrAdmins->merge([$reportingTo, auth()->user()])->filter();

    if ($isExceptMe) {
      $notifiables = $notifiables->where('id', '!=', $authUser->id);
    }

    // Send the notification
    Notification::send($notifiables, $notification);
  }

  public static function notifyHR($notification, $isExceptMe = true): void
  {
      try {
          $authUser = auth()->user();

          // Retrieve HR users for the same business
          $hrUsers = User::where('business_id', $authUser->business_id)
              ->whereHas('roles', function ($query) {
                  $query->where('name', 'hr');
              })
              ->get();

          // Include reporting manager and current user
          $extraUsers = collect([$authUser->reportingTo, $authUser])->filter();

          // Combine and remove duplicates
          $notifiables = $hrUsers->merge($extraUsers)->unique('id');

          // Optionally exclude current user
          if ($isExceptMe) {
              $notifiables = $notifiables->where('id', '!=', $authUser->id);
          }

          // Send the notification
          Notification::send($notifiables, $notification);
          
      } catch (\Exception $e) {
          Log::error('HR Notification Error: ' . $e->getMessage());
      }
  }


  public static function notifyAllExceptMe($notification): void
  {
    $authUser = auth()->user();

    // Retrieve all users except the authenticated user
    $users = User::where('id', '!=', $authUser->id)->where('business_id', $authUser->business_id)->get();

    // Send the notification
    Notification::send($users, $notification);
  }

  public static function notifySuperAdmins($notification): void
  {
    // Retrieve all super admins
    $superAdmins = User::whereHas('roles', function ($query) {
      $query->where('name', 'super_admin');
    })->get();

    // Send the notification
    Notification::send($superAdmins, $notification);
  }

  public static function notifyTenants($notification): void
  {
    // Retrieve all tenants
    $tenants = User::whereHas('roles', function ($query) {
      $query->where('name', 'customer');
    })->get();

    // Send the notification
    Notification::send($tenants, $notification);
  }

  public static function notifySuperAdminsAndTenants($notification): void
  {
    // Retrieve all super admins and tenants
    $users = User::whereHas('roles', function ($query) {
      $query->where('name', 'super_admin')->orWhere('name', 'customer');
    })->get();

    // Send the notification
    Notification::send($users, $notification);
  }

}
