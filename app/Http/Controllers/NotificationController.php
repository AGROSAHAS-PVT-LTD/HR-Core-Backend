<?php

namespace App\Http\Controllers;

use App\ApiClasses\Success;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

  public function index()
  {
    $notifications = Auth::user()->notifications;

    return view('notifications.index', compact('notifications'));
  }

  public function markAsRead()
  {
    Auth::user()->unreadNotifications->markAsRead();
    return redirect()->back();
  }

  public function marksAllAsReadOld()
  {
      Auth::user()->unreadNotifications->markAsRead();
      return redirect()->back();
  }

  public function marksAllAsRead()
  {
      $notifications = Auth::user()->notifications;

      foreach ($notifications as $notification) {
          $notification->is_read = false; // Set to false (or 0)
          $notification->save();
      }

      return redirect()->route('notifications.myNotifications')->with('success', 'All notifications updated.');
  }



  public function getNotificationsAjax()
  {
    $notifications = Auth::user()->notifications;
    return Success::response($notifications);
  }

  public function myNotifications()
  {
    $notifications = Auth::user()->notifications;
    return view('notifications.myNotifications', compact('notifications'));
  }
}
