<?php

namespace App\Http\Controllers\Api;

use App\ApiClasses\Success;
use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Constants;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
  public function getAllOld(Request $request)
  {

    $skip = $request->skip;
    $take = $request->take ?? 10;

    $query = Holiday::query()->where('business_id', $user->business_id)
      ->select('holidays.id', 'holidays.name', 'holidays.code', 'holidays.date', 'holidays.created_at', 'holidays.updated_at')
      ->orderBy('holidays.created_at', 'desc');


    if ($request->has('year')) {
      $year = $request->year;
      $query->whereYear('holidays.date', $year);
    }

    $totalCount = $query->count();

    $holidays = $query->skip($skip)->take($take)->get();

    $holidays = $holidays->map(function ($holiday) {
      return [
        'id' => $holiday->id,
        'name' => $holiday->name,
        'date' => Carbon::parse($holiday->date)->format(Constants::DateFormat),
        'created_at' => Carbon::parse($holiday->created_at)->format(Constants::DateTimeFormat),
        'updated_at' => Carbon::parse($holiday->updated_at)->format(Constants::DateTimeFormat),
      ];
    });

    $response = [
      'totalCount' => $totalCount,
      'values' => $holidays
    ];

    return Success::response($response);
  }

  public function getAll(Request $request)
  {
      try {
          $user = auth()->user();
          $skip = $request->skip ?? 0;
          $take = $request->take ?? 10;

          $query = Holiday::query()
              ->where('business_id', $user->business_id)
              ->select('id', 'name', 'code', 'date', 'created_at', 'updated_at')
              ->orderBy('created_at', 'desc');

          $query->when($request->has('year'), function ($q) use ($request) {
              $q->whereYear('date', $request->year);
          });

          $totalCount = $query->count();

          $holidays = $query->skip($skip)->take($take)->get()->map(function ($holiday) {
              return [
                  'id' => $holiday->id,
                  'name' => $holiday->name,
                  'date' => $holiday->date->format(Constants::DateFormat),
                  'created_at' => $holiday->created_at->format(Constants::DateTimeFormat),
                  'updated_at' => $holiday->updated_at->format(Constants::DateTimeFormat),
              ];
          });

          return Success::response([
              'totalCount' => $totalCount,
              'values' => $holidays,
          ]);

      } catch (\Exception $e) {
          Log::error('Failed to retrieve holidays: ' . $e->getMessage());
          return Error::response('Unable to fetch holidays at the moment.');
      }
  }

}
