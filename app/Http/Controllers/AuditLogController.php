<?php

namespace App\Http\Controllers;

use OwenIt\Auditing\Models\Audit;

class AuditLogController extends Controller
{
  public function index()
  {
    $auditLogs = Audit::where('business_id', auth()->user()->business_id)->with('user')->get();
    return view('audit-logs.index', compact('auditLogs'));
  }

  public function show($id)
  {
    $auditLog = Audit::with('user')->find($id);
    return view('audit-logs.show', compact('auditLog'));
  }
}
