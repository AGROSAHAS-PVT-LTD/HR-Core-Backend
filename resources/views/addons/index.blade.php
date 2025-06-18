@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Addons')
<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/animate-css/animate.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection
@section('content')
  <div class="container">
    <div class="row mb-4 align-items-center">
      <div class="col-md-6">
        <h4 class="fw-bold">Addons</h4>
      </div>
      <div class="col-md-6 text-end">
        {{-- Upload Button --}}
        <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#uploadSection">
          <i class="bx bx-plus me-2"></i> Add New Addon
        </button>
      </div>
    </div>
    {{-- Upload Form (Initially Collapsed) --}}
    <div class="collapse mb-4" id="uploadSection">
      <div class="card card-body shadow-sm border-0 rounded-4">
        <h5 class="mb-3 fw-semibold">Upload New Addon</h5>
        <form action="{{ route('module.upload') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <input type="file" name="module" class="form-control" accept=".zip" required>
          </div>
          <button type="submit" class="btn btn-primary">Upload</button>
        </form>
      </div>
    </div>

    {{-- Card View for Addons --}}
    <div class="row">
      @foreach ($modules as $module)
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card h-100 shadow-sm border-0 rounded-4">
            <div class="card-body d-flex flex-column justify-content-between">
              <div>
                {{-- Module Icon (optional) --}}
                <div class="mb-3">
                  <i class="bx bx-category-alt bx-lg text-primary"></i>
                </div>

                {{-- Module Name --}}
                <h5 class="card-title fw-bold text-dark">{{ $module->get('displayName') ?? $module->getName() }}</h5>

                {{-- Module Status --}}
                <p class="text-muted mb-2">Status: <span
                    class="fw-semibold"> <span
                      class="badge bg-{{ $module->isEnabled() ? 'success' : 'danger' }}">{{ $module->isEnabled() ? 'Enabled' : 'Disabled' }}</span></span>

                {{-- Module Version --}}
                <p class="text-muted mb-2">Version: <span
                    class="fw-semibold">{{$module->get('version') ?? 'N/A' }}</span></p>

                {{-- Module Description --}}
                <p class="text-muted mb-3" style="font-size: 0.9rem;">
                  {{ $module->get('description', 'No description available.') }}
                </p>
              </div>

              <div>
                {{-- Status and Actions --}}
                <div class="d-flex align-items-center justify-content-between">
                  {{-- Module Status --}}
                  <div>
                    {{-- Enable/Disable Button --}}
                    @if ($module->isEnabled())
                      <form action="{{ route('module.deactivate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="module" value="{{ $module->getName() }}">
                        <button type="submit" class="btn btn-sm btn-warning">
                          Deactivate
                        </button>
                      </form>
                    @else
                      <form action="{{ route('module.activate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="module" value="{{ $module->getName() }}">
                        <button type="submit" class="btn btn-sm btn-success">
                          Activate
                        </button>
                      </form>
                    @endif
                  </div>
                  @if(!env('APP_DEMO'))
                    {{-- Action Buttons --}}
                    <div class="d-flex gap-2">


                      {{-- Uninstall Button --}}
                      <button type="button" class="btn btn-sm uninstall-module" data-module="{{ $module->getName() }}">
                        <i class="bx bx-trash text-danger"></i>
                      </button>
                      <form id="uninstall-form-{{ $module->getName() }}" action="{{ route('module.uninstall') }}"
                            method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="module" value="{{ $module->getName() }}">
                      </form>

                    </div>
                  @endif

                  @if(env('APP_DEMO'))
                    <!-- Buy now link from Addon Array -->
                    <a href="{{ Constants::All_ADDONS_ARRAY[$module->getName()]['purchase_link'] }}" target="_blank">
                      <i class="bx bx-cart
                    me-1"></i> Buy Now </a>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Explore More Addons Section --}}
    <h5 class="fw-bold mt-5">
      <i class="bx bx-compass me-2"></i>
      @lang('Explore More Addons')</h5>
    <p class="text-muted mb-4">Discover more addons to enhance your application.</p>
    {{-- Addons Card View --}}
    <div class="row">
      @foreach (Constants::All_ADDONS_ARRAY as $addonKey => $addon)
        @if(Module::has($addonKey))
          @continue
        @endif
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card h-100 shadow-sm border-0 rounded-4">
            <div class="card-body d-flex flex-column justify-content-between">
              <div>
                {{-- Addon Name --}}
                <h5 class="card-title fw-bold text-dark">{{ $addon['name'] }}</h5>
                {{-- Addon Description --}}
                <p class="text-muted mb-3" style="font-size: 0.9rem;">{{ $addon['description'] }}</p>
              </div>
              {{-- Buy Now Button --}}
              <div class="text-start">
                <a href="{{ $addon['purchase_link'] }}" target="_blank" class="btn btn-primary">
                  <i class="bx bx-cart me-1"></i> Buy Now
                </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.uninstall-module').forEach(button => {
        button.addEventListener('click', function () {
          const moduleName = this.getAttribute('data-module');
          const uninstallForm = document.getElementById(`uninstall-form-${moduleName}`);

          Swal.fire({
            title: 'Are you sure?',
            text: `You are about to uninstall the "${moduleName}" module. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, uninstall it!',
            cancelButtonText: 'Cancel',
            customClass: {
              confirmButton: 'btn btn-primary me-3',
              cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
          }).then((result) => {
            if (result.isConfirmed) {
              uninstallForm.submit();
            }
          });
        });
      });
    });

  </script>
@endsection
