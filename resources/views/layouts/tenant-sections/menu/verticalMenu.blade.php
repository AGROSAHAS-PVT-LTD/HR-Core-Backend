@php
  use App\Services\AddonService\IAddonService;use Illuminate\Support\Facades\Route;
  $configData = Helper::appClasses();
  $modules = \Nwidart\Modules\Facades\Module::all();
  $addonService = app(IAddonService::class);

@endphp

@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- ! Hide app brand if navbar-full -->

  @if(!isset($navbarFull))
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <span class="app-brand-logo demo">
        @if(empty($settings->company_name))
          <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" width="27">
        @endif
      </span>
      <span class="app-brand-text demo menu-text fw-bold ms-2" 
            style="font-size: 1rem; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block;">
        {{ auth()->check() && auth()->user()->business ? auth()->user()->business->name : ($settings->company_name ?? config('variables.templateName')) }}
      </span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
    </a>
  </div>
@endif


  <div class="menu-inner-shadow"></div>
  <ul class="menu-inner py-1">
    @foreach ($menuData[3]->menu as $menu)

     @if(isset($menu->is_superadmin))
        @php
          if(!auth()->user()->is_superuser ){
            continue;
          }
        @endphp
      @endif


      @if(isset($menu->addon))
        @php

          if(!$addonService->isAddonEnabled($menu->addon)){
            continue;
          }
        @endphp
      @endif

      @if(isset($menu->standardAddon))
        @php
          if(!in_array($menu->standardAddon, $settings->available_modules)){
            continue;
          }
        @endphp
      @endif

      {{-- adding active and open class if child is active --}}

      {{-- menu headers --}}
      @if (isset($menu->menuHeader))
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
        </li>
      @else

        {{-- active menu method --}}
        @php
          $activeClass = null;
          $currentRouteName = Route::currentRouteName();

          if ($currentRouteName === $menu->slug) {
            $activeClass = 'active';
          }
          elseif (isset($menu->submenu)) {
            if (gettype($menu->slug) === 'array') {
              foreach($menu->slug as $slug){
                if (str_contains($currentRouteName,$slug) and strpos($currentRouteName,$slug) === 0) {
                  $activeClass = 'active open';
                }
              }
            }
            else{
              if (str_contains($currentRouteName,$menu->slug) and strpos($currentRouteName,$menu->slug) === 0) {
                $activeClass = 'active open';
              }
            }
          }
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{$activeClass}}">
          <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
             class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
             @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
            @isset($menu->icon)
              <i class="{{ $menu->icon }}"></i>
            @endisset
            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
            @isset($menu->badge)
              <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
            @endisset
          </a>

          {{-- submenu --}}
          @isset($menu->submenu)
            @include('layouts.sections.menu.submenu',['menu' => $menu->submenu])
          @endisset
        </li>
      @endif
    @endforeach
  </ul>

</aside>
