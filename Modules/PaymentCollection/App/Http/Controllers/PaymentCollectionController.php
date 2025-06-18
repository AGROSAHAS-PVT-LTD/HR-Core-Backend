<?php

namespace Modules\PaymentCollection\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentCollection;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentCollectionController extends Controller
{

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $employees = User::whereNotNull('shift_id')
      ->whereNotNull('team_id')
      ->select('id', 'first_name', 'last_name')
      ->get();

    $paymentCollections = PaymentCollection::with('user')
      ->with('client')
      ->orderBy('created_at', 'desc')
      ->get();

    return view('paymentcollection::index', compact('employees'), compact('paymentCollections'));
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return view('paymentcollection::create');
  }


  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    return view('paymentcollection::show');
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit($id)
  {
    return view('paymentcollection::edit');
  }
}
