<?php

namespace Modules\TaskSystem\App\Http\Controllers;

use App\Helpers\PushHelper;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Task;
use App\Models\TaskUpdate;
use App\Models\User;
use Constants;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskSystemController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $tasks = Task::with('user')->get();

    return view('tasksystem::index', compact('tasks'));
  }

  public function taskView()
  {
    $tasks = Task::with('user')
      ->with('client')
      ->with('taskUpdates')
      ->where('status', 'in_progress')
      ->orWhere('status', 'hold')
      ->get();

    return view('tasksystem::taskView', compact('tasks'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    $employees = User::where('shift_id', '!=', null)->get();

    $clients = Client::all();

    return view('tasksystem::create', compact('clients'), compact('employees'));
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request): RedirectResponse
  {
    $taskType = $request->TaskType;

    if ($taskType == null || $taskType == '') {
      return redirect()->back()->with('error', 'Please select a task type');
    }

    $employee = $request->EmployeeId;

    if ($employee == null || $employee == '') {
      return redirect()->back()->with('error', 'Please select an employee');
    }

    $client = $request->ClientId;

    if ($taskType == '1' && $client == null) {
      //return with data
      return redirect()->back()->with('error', 'Please select a client');
    }

    $latitude = $request->Latitude;

    $longitude = $request->Longitude;

    $maxRadius = $request->MaxRadius;

    if ($latitude == null || $latitude == '' || $longitude == null || $longitude == '' || $maxRadius == null || $maxRadius == '') {
      return redirect()->back()->with('error', 'Invalid location');
    }

    $title = $request->Title;

    if ($title == null || $title == '') {
      return redirect()->back()->with('error', 'Please enter a title');
    }

    $description = $request->Description;

    if ($description == null || $description == '') {
      return redirect()->back()->with('error', 'Please enter a description');
    }

    $forDate = $request->ForDate;

    if ($forDate == null || $forDate == '') {
      return redirect()->back()->with('error', 'Please enter a date');
    }

    //Check if the date is in the past
    if (strtotime($forDate) < strtotime(date('Y-m-d'))) {
      return redirect()->back()->with('error', 'Date cannot be in the past');
    }

    //Check same datetime task already exists for the employee

    $taskExists = Task::where('user_id', $employee)
      ->where('for_date', $forDate)
      ->where('status', '==', 'new')
      ->first();

    if ($taskExists) {
      return redirect()->back()->with('error', 'Task already exists for the employee');
    }

    $task = new Task();
    $task->title = $title;
    $task->description = $description;
    $task->type = $taskType == '1' ? 'client_based' : 'open';
    $task->assigned_by_id = auth()->id();
    $task->user_id = $employee;
    $task->client_id = $taskType == '1' ? $client : null;
    $task->for_date = $forDate;
    $task->status = 'new';
    $task->latitude = $latitude;
    $task->longitude = $longitude;
    $task->max_radius = $maxRadius;
    $task->save();

    $pushHelper = new PushHelper();

    $pushHelper->sendNotificationToUser($employee, 'Task Assigned', 'Task assigned to you by Admin');

    return redirect()->route('task.index')->with('success', 'Task created successfully');
  }

  public function activity($id)
  {
    $task = Task::with('user')
      ->with('client')
      ->with('taskUpdates')
      ->find($id);

    return view('tasksystem::activity', compact('task'));
  }

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    return view('tasksystem::show');
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit($id)
  {
    return view('tasksystem::edit');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    $task = Task::find($id);
    $task->delete();

    return redirect()->route('task.index')->with('success', 'Task deleted successfully');
  }


  public function addTaskUpdate(Request $request)
  {
    $messageType = $request->MessageType;
    $comments = $request->Comments;

    $file = $request->file('File');

    $taskId = $request->TaskId;

    if ($taskId == null || $taskId == '') {
      return redirect()->back()->with('error', 'Invalid task');
    }

    if ($messageType == null || $messageType == '') {
      return redirect()->back()->with('error', 'Please select a message type');
    }

    if ($messageType == '1' && ($comments == null || $comments == '')) {
      return redirect()->back()->with('error', 'Please enter comments');
    }

    $task = Task::find($taskId);

    if ($task == null) {
      return redirect()->back()->with('error', 'Invalid task');
    }

    if ($messageType == '2' && ($file == null)) {
      return redirect()->back()->with('error', 'Please select a file');
    }

    $fileName = null;
    if ($messageType == '2') {
      $fileName = time() . '_' . $file->getClientOriginalName();

      Storage::disk('public')->putFileAs(Constants::BaseFolderTaskUpdateFiles, $file, $fileName);
    }

    $taskUpdate = new TaskUpdate();
    $taskUpdate->task_id = $taskId;
    $taskUpdate->update_type = $messageType == '1' ? 'comment' : 'document';
    $taskUpdate->comment = $messageType == '1' ? $comments : null;
    $taskUpdate->file_url = $messageType == '2' ? $fileName : null;
    $taskUpdate->created_by_id = auth()->id();
    $taskUpdate->is_admin = true;
    $taskUpdate->save();

    $pushHelper = new PushHelper();

    $pushHelper->sendNotificationToUser($task->user_id, 'Task Update', 'Task updated by Admin');

    return redirect()->back()->with('success', 'Task updated successfully');
  }

  public function getClientLocation(Request $request)
  {
    try {
      $client = Client::find($request->clientId);

      if ($client == null) {
        return response()->json('Client not found', 400);
      }

      return response()->json($client);
    } catch (Exception $exception) {
      return response()->json($exception->getMessage(), 400);
    }
  }
}
