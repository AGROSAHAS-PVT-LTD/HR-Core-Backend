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
