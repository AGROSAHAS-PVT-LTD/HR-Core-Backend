<?php

namespace Modules\PaymentCollection\App\Http\Controllers\Api;

use App\ApiClasses\Success;
use App\Helpers\PushHelper;
use App\Http\Controllers\Controller;
use App\Models\PaymentCollection;
use Constants;
use Illuminate\Http\Request;

class PaymentCollectionApiController extends Controller
{

  public function create(Request $request)
  {
    $clientId = $request->clientId;
    $paymentType = $request->paymentType;
    $amount = $request->amount;
    $remarks = $request->remarks;

    $paymentCollection = new PaymentCollection();
    $paymentCollection->client_id = $clientId;
    $paymentCollection->payment_mode = $this->getPaymentType($paymentType);
    $paymentCollection->amount = $amount;
    $paymentCollection->remarks = $remarks;
    $paymentCollection->user_id = auth()->user()->id;
    $paymentCollection->save();


    $pushHelper = new PushHelper();

    $pushHelper->sendNotificationToAdmin('Payment collected', 'Payment collected from ' . $paymentCollection->client->name . ' by ' . auth()->user()->getFullName());


    return Success::response('Payment collection created successfully');
  }

  private function getPaymentType($type)
  {
    $paymentType = 'other';
    if ($type == 'Cash') {
      $paymentType = 'cash';
    } else if ($type == 'Cheque') {
      $paymentType = 'cheque';
    } else if ($type == 'Online') {
      $paymentType = 'online';
    }

    return $paymentType;
  }

  public function getAll(Request $request)
  {
    $skip = $request->skip;
    $take = $request->take ?? 10;

    $paymentQuery = PaymentCollection::query()
      ->where('user_id', auth()->user()->id)
      ->with('client')
      ->orderBy('id', 'desc');

    if ($request->has('date') && !empty($request->date)) {
      $paymentQuery->whereDate('created_at', $request->date);
    }

    $totalCount = $paymentQuery->count();

    $paymentCollections = $paymentQuery->skip($skip)->take($take)->get();

    $collections = [];

    foreach ($paymentCollections as $paymentCollection) {
      $collections[] = [
        'id' => $paymentCollection->id,
        'clientId' => $paymentCollection->client_id,
        'client' => [
          'id' => $paymentCollection->client->id,
          'name' => $paymentCollection->client->name,
          'address' => $paymentCollection->client->address,
          'latitude' => floatval($paymentCollection->client->latitude),
          'longitude' => floatval($paymentCollection->client->longitude),
          'phoneNumber' => $paymentCollection->client->phone,
          'email' => $paymentCollection->client->email,
          'contactPerson' => $paymentCollection->client->contact_person_name,
          'city' => $paymentCollection->client->city,
          'status' => $paymentCollection->client->status,
          'createdAt' => $paymentCollection->client->created_at->format(Constants::DateTimeFormat),
        ],
        'paymentType' => $paymentCollection->payment_mode,
        'amount' => floatval($paymentCollection->amount),
        'remarks' => $paymentCollection->remarks,
        'createdAt' => $paymentCollection->created_at->format(Constants::DateTimeFormat),
      ];
    }

    $response = [
      'totalCount' => $totalCount,
      'values' => $collections
    ];

    return Success::response($response);
  }
}
