<?php

namespace Modules\TaskSystem\App\Http\Controllers\Api;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Helpers\PushHelper;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskUpdate;
use Carbon\Carbon;
use Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use function DateTime;

class TaskSystemApiController extends Controller
{
  public function updateStatus(Request $request)
  {
    $taskId = $request->taskId;

    $task = Task::find($taskId);

    if ($task == null) {
      return Error::response('Task not found');
    }

    if ($task->status != 'in_progress') {
      return Error::response('Task not started');
    }

    $taskUpdateType = strtolower($request->taskUpdateType);
    $latitude = $request->latitude;
    $longitude = $request->longitude;

    if ($latitude == null || $latitude == '' || $longitude == null || $longitude == '') {
      return Error::response('Location is required');
    }

    if ($taskUpdateType == null || $taskUpdateType == '') {
      return Error::response('Task update type is required');
    }

    if ($taskUpdateType != 'comment' && $taskUpdateType != 'location') {
      return Error::response('Invalid task update type');
    }

    $comment = $request->comment;

    if ($taskUpdateType == 'comment' && ($comment == null || $comment == '')) {
      return Error::response('Comment is required');
    }

    $taskUpdate = new TaskUpdate();
    $taskUpdate->task_id = $task->id;
    $taskUpdate->latitude = $latitude;
    $taskUpdate->longitude = $longitude;
    $taskUpdate->created_by_id = auth()->user()->id;
    $taskUpdate->update_type = $taskUpdateType;
    $taskUpdate->comment = $comment;
    $taskUpdate->save();

    $pushHelper = new PushHelper();

    $pushHelper->sendNotificationToAdmin('Task Update', 'Task updated by ' . auth()->user()->getFullName());

    return Success::response('Task updated successfully');
  }

  public function updateStatusFile(Request $request)
  {
    $taskId = $request->taskId;

    $task = Task::find($taskId);

    if ($task == null) {
      return Error::response('Task not found');
    }

    if ($task->status != 'in_progress') {
      return Error::response('Task not started');
    }

    $file = $request->file('file');

    if ($file == null) {
      return Error::response('File is required');
    }

    $latitude = $request->latitude;
    $longitude = $request->longitude;

    if ($latitude == null || $latitude == '' || $longitude == null || $longitude == '') {
      return Error::response('Location is required');
    }

    $taskUpdateType = strtolower($request->taskUpdateType);

    if ($taskUpdateType == null || $taskUpdateType == '') {
      return Error::response('Task update type is required');
    }

    if ($taskUpdateType != 'document' && $taskUpdateType != 'image') {
      return Error::response('Invalid task update type');
    }

    $fileName = time() . '_' . $file->getClientOriginalName();

    Storage::disk('public')->putFileAs(Constants::BaseFolderTaskUpdateFiles, $file, $fileName);

    $taskUpdate = new TaskUpdate();
    $taskUpdate->task_id = $task->id;
    $taskUpdate->latitude = $latitude;
    $taskUpdate->longitude = $longitude;
    $taskUpdate->created_by_id = auth()->user()->id;
    $taskUpdate->update_type = $taskUpdateType;
    $taskUpdate->file_url = $fileName;
    $taskUpdate->save();

    $pushHelper = new PushHelper();

    $pushHelper->sendNotificationToAdmin('Task File Update', 'Task updated by ' . auth()->user()->getFullName());

    return Success::response('Task updated successfully');
  }

  public function getTaskUpdates(Request $request)
  {
    $taskId = $request->taskId;

    if ($taskId == null || $taskId == '') {
      return Error::response('Task id is required');
    }

    $taskUpdates = TaskUpdate::where('task_id', $taskId)
      ->get();

    $result = [];

    foreach ($taskUpdates as $taskUpdate) {
      $result[] = [
        'id' => $taskUpdate->id,
        'comment' => $taskUpdate->comment,
        'latitude' => floatval($taskUpdate->latitude),
        'longitude' => floatval($taskUpdate->longitude),
        'address' => $taskUpdate->address,
        'fileUrl' => $taskUpdate->file_url != null ? asset('storage/'.Constants::BaseFolderTaskUpdateFiles . $taskUpdate->file_url) : null,
        'taskUpdateType' => $taskUpdate->update_type == 'un_hold' ? 'unhold' : $taskUpdate->update_type,
        'isFromAdmin' => $taskUpdate->is_admin == 1,
        'createdAt' => Carbon::parse($taskUpdate->created_at)->format(Constants::DateTimeFormat),
      ];
    }

    return Success::response($result);

  }

  public function getTasks()
  {
    $tasks = Task::where('user_id', auth()->user()->id)
      ->with(['client', 'user'])
      ->get();

    $result = [];

    foreach ($tasks as $task) {

      $date = Carbon::parse($task->for_date);

      $result[] = [
        'id' => $task->id,
        'forDate' => $date->format('d-m-Y h:i A'),
        'startDateTime' => Carbon::parse($task->start_date_time)->format('d-m-Y h:i A'),
        'endDateTime' => Carbon::parse($task->end_date_time)->format('d-m-Y h:i A'),
        'status' => $task->status == 'new' ? 'new' : ($task->status == 'in_progress' ? 'inprogress' : $task->status),
        'assignedById' => $task->assigned_by_id,
        'clientId' => $task->client_id,
        'client' => $task->client != null ? [
          'id' => $task->client->id,
          'name' => $task->client->name,
          'address' => $task->client->address,
          'phoneNumber' => $task->client->phone,
          'email' => $task->client->email,
          'latitude' => floatval($task->client->latitude),
          'longitude' => floatval($task->client->longitude),
        ] : null,
        'userId' =>  $task->user->id,
        'userName' => $task->user->getFullName(),
        'description' => $task->description,
        'isGeoFenceEnabled' => $task->is_geo_fence_enabled,
        'latitude' => $task->latitude,
        'longitude' => $task->longitude,
        'maxRadius' => $task->max_radius,
        'taskType' => $task->type,
        'title' => $task->title,
      ];
    }

    return Success::response($result);

  }

  public function getManagerTasks()
  {
    $tasks = Task::with(['client', 'user'])
      ->get();

    $result = [];

    foreach ($tasks as $task) {

      $date = Carbon::parse($task->for_date);

      $result[] = [
        'id' => $task->id,
        'forDate' => $date->format('d-m-Y h:i A'),
        'startDateTime' => Carbon::parse($task->start_date_time)->format('d-m-Y h:i A'),
        'endDateTime' => Carbon::parse($task->end_date_time)->format('d-m-Y h:i A'),
        'status' => $task->status == 'new' ? 'new' : ($task->status == 'in_progress' ? 'inprogress' : $task->status),
        'assignedById' => $task->assigned_by_id,
        'clientId' => $task->client_id,
        'client' => $task->client != null ? [
          'id' => $task->client->id,
          'name' => $task->client->name,
          'address' => $task->client->address,
          'phoneNumber' => $task->client->phone,
          'email' => $task->client->email,
          'latitude' => floatval($task->client->latitude),
          'longitude' => floatval($task->client->longitude),
        ] : null,
        'userId' =>  $task->user->id,
        'userName' => $task->user->getFullName(),
        'description' => $task->description,
        'isGeoFenceEnabled' => $task->is_geo_fence_enabled,
        'latitude' => $task->latitude,
        'longitude' => $task->longitude,
        'maxRadius' => $task->max_radius,
        'taskType' => $task->type,
        'title' => $task->title,
      ];
    }

    return Success::response($result);

  }

  public function startTask(Request $request)
  {
    $taskId = $request->taskId;

    $task = Task::find($taskId);

    if ($task == null) {
      return Error::response('Task not found');
    }

    if ($task->status != 'new') {
      return Error::response('Task already started');
    }

    $runningTasks = Task::where('user_id', auth()->id())
      ->where('status', 'in_progress')
      ->count();

    if ($runningTasks > 0) {
      return Error::response('You have already running task');
    }
    $latitude = $request->latitude;

    $longitude = $request->longitude;

    if ($latitude == null || $latitude == '' || $longitude == null || $longitude == '') {
      return Error::response('Invalid location');
    }

    $task->status = 'in_progress';
    $task->start_date_time = now();
    $task->save();

    $user = auth()->user();

    $taskUpdate = new TaskUpdate();
    $taskUpdate->task_id = $task->id;
    $taskUpdate->latitude = $latitude;
    $taskUpdate->longitude = $longitude;
    $taskUpdate->created_by_id = $user->id;
    $taskUpdate->update_type = 'start';
    $taskUpdate->save();

    $pushHelper = new PushHelper();

    $pushHelper->sendNotificationToAdmin('Task started', 'Task started by ' . $user->getFullName());


    return Success::response('Task started successfully');
  }

  public function holdTask(Request $request)
  {
    $taskId = $request->taskId;

    $task = Task::find($taskId);

    if ($task == null) {
      return Error::response('Task not found');
    }

    if ($task->status != 'in_progress') {
      return Error::response('Task already started');
    }

    $latitude = $request->latitude;

    $longitude = $request->longitude;

    if ($latitude == null || $latitude == '' || $longitude == null || $longitude == '') {
      return Error::response('Invalid location');
    }

    $task->status = 'hold';
    $task->save();

    $user = auth()->user();

    $taskUpdate = new TaskUpdate();
    $taskUpdate->task_id = $task->id;
    $taskUpdate->latitude = $latitude;
    $taskUpdate->longitude = $longitude;
    $taskUpdate->created_by_id = $user->id;
    $taskUpdate->update_type = 'hold';
    $taskUpdate->save();

    $pushHelper = new PushHelper();

    $pushHelper->sendNotificationToAdmin('Task hold', 'Task hold by ' . $user->getFullName());

    return Success::response('Task hold successfully');
  }

  public function resumeTask(Request $request)
  {
    $taskId = $request->taskId;

    $task = Task::find($taskId);

    if ($task == null) {
      return Error::response('Task not found');
    }

    if ($task->status != 'hold') {
      return Error::response('Task not on hold');
    }

    $latitude = $request->latitude;

    $longitude = $request->longitude;

    if ($latitude == null || $latitude == '' || $longitude == null || $longitude == '') {
      return Error::response('Invalid location');
    }

    $user = auth()->user();

    //Check for already running tasks
    $runningTasks = Task::where('user_id', $user->id)
      ->where('status', 'in_progress')
      ->count();

    if ($runningTasks > 0) {
      return Error::response('You have already running task');
    }

    $task->status = 'in_progress';
    $task->save();

    $taskUpdate = new TaskUpdate();
    $taskUpdate->task_id = $task->id;
    $taskUpdate->latitude = $latitude;
    $taskUpdate->longitude = $longitude;
    $taskUpdate->created_by_id = $user->id;
    $taskUpdate->update_type = 'un_hold';
    $taskUpdate->save();

    $pushHelper = new PushHelper();

    $pushHelper->sendNotificationToAdmin('Task resumed', 'Task resumed by ' . $user->getFullName());

    return Success::response('Task resumed successfully');
  }

  public function getUserTasks(Request $request)
  {
    $userId = $request->user_id;
    
    if (!$userId) {
      return Error::response('User ID is required');
    }

    $tasks = Task::where('user_id', $userId)
      ->with(['client', 'user'])
      ->get();

    $result = [];

    foreach ($tasks as $task) {
      $date = Carbon::parse($task->for_date);

      $result[] = [
        'id' => $task->id,
        'forDate' => $date->format('d-m-Y h:i A'),
        'startDateTime' => Carbon::parse($task->start_date_time)->format('d-m-Y h:i A'),
        'endDateTime' => Carbon::parse($task->end_date_time)->format('d-m-Y h:i A'),
        'status' => $task->status == 'new' ? 'new' : ($task->status == 'in_progress' ? 'inprogress' : $task->status),
        'assignedById' => $task->assigned_by_id,
        'clientId' => $task->client_id,
        'client' => $task->client != null ? [
          'id' => $task->client->id,
          'name' => $task->client->name,
          'address' => $task->client->address,
          'phoneNumber' => $task->client->phone,
          'email' => $task->client->email,
          'latitude' => floatval($task->client->latitude),
          'longitude' => floatval($task->client->longitude),
        ] : null,

        'userId' =>  $task->user->id,
        'userName' => $task->user->getFullName(),

        'isGeoFenceEnabled' => $task->is_geo_fence_enabled,
        'latitude' => $task->latitude,
        'longitude' => $task->longitude,
        'maxRadius' => $task->max_radius,
        'taskType' => $task->type,
        'title' => $task->title,
      ];
    }

    return Success::response($result);
  }

  public function getGroupUsersWithTasks()
  {
    $usersWithTasks = \App\Models\User::whereHas('tasks')
      ->select('id', 'first_name', 'last_name')
      ->withCount('tasks')
      ->get();

    $result = [];
    foreach ($usersWithTasks as $user) {
      $result[] = [
        'id' => $user->id,
        'name' => $user->first_name . ' ' . $user->last_name,
        'taskCount' => $user->tasks_count
      ];
    }

    return Success::response($result);
  }

  public function completeTask(Request $request)
  {
    $taskId = $request->taskId;

    $task = Task::find($taskId);

    if ($task == null) {
      return Error::response('Task not found');
    }

    if ($task->status != 'in_progress') {
      return Error::response('Task not started');
    }

    $latitude = $request->latitude;

    $longitude = $request->longitude;

    if ($latitude == null || $latitude == '' || $longitude == null || $longitude == '') {
      return Error::response('Location is required');
    }

    $task->status = 'completed';
    $task->end_date_time = now();
    $task->save();

    $user = auth()->user();

    $taskUpdate = new TaskUpdate();
    $taskUpdate->task_id = $task->id;
    $taskUpdate->latitude = $latitude;
    $taskUpdate->longitude = $longitude;
    $taskUpdate->created_by_id = $user->id;
    $taskUpdate->update_type = 'complete';
    $taskUpdate->save();

    $pushHelper = new PushHelper();

    $pushHelper->sendNotificationToAdmin('Task completed', 'Task completed by ' . $user->getFullName());

    return Success::response('Task completed successfully');
  }
}
