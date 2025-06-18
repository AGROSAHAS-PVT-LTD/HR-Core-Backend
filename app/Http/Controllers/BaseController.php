<?php

namespace App\Http\Controllers;

use App\Models\User;
use Constants;
use Illuminate\Http\Request;

class BaseController extends Controller
{

  public function accessDenied()
  {
    $pageConfigs = ['myLayout' => 'blank', 'displayCustomizer' => false];
    return view('access-denied', ['pageConfigs' => $pageConfigs]);
  }

  public function Index()
  {
    $pageConfigs = ['myLayout' => 'blank', 'displayCustomizer' => false];
    return view('activation.index', ['pageConfigs' => $pageConfigs]);
  }

  public function activate(Request $request)
  {
    return redirect()->route('login');
  }

  public function getSearchDataAjax()
  {
    //Get json file from resources/menu
    $menuJson = file_get_contents(base_path('resources/menu/tenantVerticalMenu.json'));

    // Decode JSON into an associative array
    $menuData = json_decode($menuJson, true);

    $menuItems = $menuData['menu'];

    $response[] = [];

    //Populate pages
    $pages = [];
    foreach ($menuItems as $item) {
      if (isset($item['menuHeader'])) {
        continue;
      }
      //Check if item has submenu
      if (isset($item['submenu'])) {
        foreach ($item['submenu'] as $subItem) {
          $itemColl = collect($subItem);
          //Remove first / from url
          $url = substr($itemColl->get('url'), 1);
          $pages[] = [
            'name' => $itemColl->get('name'),
            'url' => $url,
            'icon' => $itemColl->get('icon'),
          ];
        }
      } else {
        $itemColl = collect($item);
        //Remove first / from url
        $url = substr($itemColl->get('url'), 1);
        $pages[] = [
          'name' => $itemColl->get('name'),
          'url' => $url,
          'icon' => $itemColl->get('icon'),
        ];
      }
    }

    $response = [
      'pages' => $pages,
    ];

    $users = User::whereNot('id', auth()->user()->id)->get();

    $members = [];
    foreach ($users as $user) {
      $members[] = [
        'name' => $user->getFullName(),
        'subtitle' => $user->email,
        'src' => $user->profile_picture ? asset(Constants::BaseFolderEmployeeProfileWithSlash . $user->profile_picture) : null,
        'initial' => $user->getInitials(),
        'url' => 'employees/view/' . $user->id,
      ];
    }

    $response['members'] = $members;

    return response()->json($response);
  }
}
